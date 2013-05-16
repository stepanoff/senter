<?php
class SenterZendeskDriver extends SenterDriverBase {

    const MAX_ISSUES = 100;

	protected $name = 'zendesk';
	protected $title = 'ZenDesk';
	protected $attributes = array();

    protected $issueModel = 'ZendeskIssue';

    public function init($component, $options = array()) {
        parent::init($component, $options);
    }


    public function createNewIssues ()
    {
        $data = curlWrap("/tickets/recent.json", null, "GET");

        if ($data && is_object($data)) {
            /*
            if ($this->addError('500', 'не удалось получить новые тикеты')) {
                return false;
            }
            */
            // ищем в базе, если нет, добавляем
            foreach ($data->tickets as $ticket) {
                $modelName = $this->issueModel;

                $zendeskIssue = $modelName::model()->byExternalId($ticket->id)->find();
                if (!$zendeskIssue) {
                    $zendeskIssue = new ZendeskIssue;
                    $zendeskIssue->externalId = $ticket->id;
                    $zendeskIssue->status = self::convertStatus($ticket->status);
                    $zendeskIssue->save();
                }

                $attrs = array(
                    'clientSourceId' => $zendeskIssue->id,
                    'clientSource' => $this->getDriverName(),
                    'status' => self::getIssueStatus($zendeskIssue->status),
                    'title' => $ticket->subject,
                    'body' => $ticket->description,
                    'labels' => $ticket->tags,
                    'organization' => $ticket->organization_id,
                    'requester' => $ticket->requester_id,
                );
                $this->getComponent()->addIssue($attrs);
            }
        }
    }

    /*
     * Выгружает все тикеты во внутреннюю базу
     */
    public function createIssues ()
    {
        $page = 14;
        $data = curlWrap("/tickets.json?page=".$page, null, "GET");

        if ($data && is_object($data)) {
            if ($data->error) {
                if ($this->addError('500', 'не удалось получить новые тикеты')) {
                    return false;
                }
            }

            $totalPages = ceil($data->count / 100);

            while ($page <= $totalPages) {
                // ищем в базе, если нет, добавляем
                foreach ($data->tickets as $ticket) {
                    if (!$ticket->subject) {
                        continue;
                    }

                    $modelName = $this->issueModel;
                    $zendeskIssue = $modelName::model()->byExternalId($ticket->id)->find();
                    if (!$zendeskIssue) {
                        $zendeskIssue = new ZendeskIssue;
                        $zendeskIssue->externalId = $ticket->id;
                        $zendeskIssue->status = self::convertStatus($ticket->status);
                        $zendeskIssue->save();
                    }

                    $attrs = array(
                        'clientSourceId' => $zendeskIssue->id,
                        'clientSource' => $this->getDriverName(),
                        'status' => self::getIssueStatus($zendeskIssue->status),
                        'title' => $ticket->subject,
                        'body' => $ticket->description,
                    );
                    $this->getComponent()->addIssue($attrs);
                }
                $page++;
                $data = curlWrap("/tickets.json?page=".$page, null, "GET");
            }

        }

    }


    public function getOrganizations ()
    {
        $res = array();
        $data = curlWrap("/organizations.json", null, "GET");

        if ($data && is_object($data) && $data->organizations) {
            foreach ($data->organizations as $org) {
                $item = array (
                    'name' => $org->name,
                    'externalId' => $org->id
                );
                $res[] = $item;
            }
        }
        return $res;
    }

    public function getRequesters ()
    {
        $res = array();
        $data = curlWrap("/users.json", null, "GET");

        if ($data && is_object($data) && $data->users) {
            foreach ($data->users as $user) {
                $item = array (
                    'name' => $user->name,
                    'externalId' => $user->id,
                    'orgId' => $user->organization_id,
                );
                $res[] = $item;
            }
        }
        return $res;
    }

    public function statusTypes ($source = false)
    {
        switch ($source) {
            case 'Issue':
                return array (
                    ZendeskIssue::STATUS_NEW => Issue::STATUS_NEW,
                    ZendeskIssue::STATUS_OPEN => Issue::STATUS_PROCESS,
                    ZendeskIssue::STATUS_PENDING => Issue::STATUS_PROCESS,
                    ZendeskIssue::STATUS_HOLD => Issue::STATUS_PROCESS,
                    ZendeskIssue::STATUS_SOLVED => Issue::STATUS_SOLVED,
                    ZendeskIssue::STATUS_CLOSED => Issue::STATUS_PRODUCTION,
                );
                break;

            default:
                return array (
                    ZendeskIssue::STATUS_NEW => 'new',
                    ZendeskIssue::STATUS_OPEN => 'open',
                    ZendeskIssue::STATUS_PENDING => 'pending',
                    ZendeskIssue::STATUS_HOLD => 'hold',
                    ZendeskIssue::STATUS_SOLVED => 'solved',
                    ZendeskIssue::STATUS_CLOSED => 'closed',
                );
                break;
        }
    }


    public function markIssue ($issue, $action)
    {
        $modelName = $this->issueModel;
        $zendeskIssue = $modelName::model()->findByPk($issue->clientSourceId);
        if (!$zendeskIssue)
            return false;

        $updateStatus = true;
        switch ($action) {
            case Issue::ACTION_SOLVED:
                $res = curlWrap("/tickets/".$zendeskIssue->externalId.".json", CJSON::encode(array(
                    "ticket" => array(
                         'comment' => array(
                             'type' => 'Comment',
                             'body' => 'Проверка и тестирование задачи завершено. Задача ждет выкатки обновлений на живой сайт.',
                             'public' => true,
                         ),
                    ))), "PUT");
                $zendeskIssue->status = ZendeskIssue::STATUS_SOLVED;
                if (!$res) {
                    $updateStatus = false;
                }
                break;

            case Issue::ACTION_REVIEW:
                $res = curlWrap("/tickets/".$zendeskIssue->externalId.".json", CJSON::encode(array(
                    "ticket" => array(
                         'comment' => array(
                             'type' => 'Comment',
                             'body' => 'Задача передана на проверку. После прохождения проверки, задача будет влита в основной код сайта.',
                             'public' => true,
                         ),
                    ))), "PUT");
                if (!$res) {
                   $updateStatus = false;
                }
                break;

            case Issue::ACTION_PROCESS:
                $res = curlWrap("/tickets/".$zendeskIssue->externalId.".json", CJSON::encode(array(
                    "ticket" => array(
                        'status' => 'open',
                        'comment' => array(
                            'type' => 'Comment',
                            'body' => 'Задача взята разраотчиком в разработку. После выполнения и тестирования, задача будет влита в основной код сайта.',
                            'public' => true,
                         ),
                    ))), "PUT");
                $zendeskIssue->status = ZendeskIssue::STATUS_OPEN;
                if (!$res) {
                   $updateStatus = false;
                }
                break;
        }

        if ($updateStatus) {
            $zendeskIssue->save();
            return parent::markIssue($issue, $action);
        }
        return false;
    }


    public function uploadOpenIssuesToDev ()
    {
        $limit = self::MAX_ISSUES;
        $offset = 0;
        $criteria = new CDbCriteria(array(
            'limit' => $limit,
            'offset' => $offset,
        ));
        $openedTickets = ZendeskIssue::model()->inProcess()->findAll($criteria);

        while ($openedTickets) {
            $tmp = array();
            foreach ($openedTickets as $t) {
                $tmp[$t->externalId] = $t;
            }
            $openedTickets = $tmp;

            // берем эти тикеты с зендеска, смотрим какие надо переоткрыть, какие отправить на гитхаб
            $zdTickets = curlWrap("/tickets/show_many.json?ids=".implode(',', array_keys($openedTickets))."", null, "GET");
            foreach ($zdTickets->tickets as $zdTicket) {
                if ($zdTicket->tags) {
                    $ticket = $openedTickets[$zdTicket->id];
                    $ticketBody = $zdTicket->description;

                    $sendToDev = false;
                    foreach ($zdTicket->tags as $tag) {
                        $sendData = self::getSendToDevDataFromTag($tag);
                        if ($sendData) {
                            $audit = curlWrap("/tickets/".$zdTicket->id."/audits.json", null, "GET");
                            if ($audit && $audit->audits) {
                                foreach ($audit->audits as $a) {
                                    $break = false;
                                    foreach ($a->events as $e) {
                                        if ($e->type == 'Comment' && !$e->public) {
                                            $ticketBody = "```\n".$ticketBody."\n```\n\n\n".$e->body;
                                        }
                                    }
                                    if ($break)
                                        break;
                                }
                            }
                            $sendToDev = true;
                            break;
                        }
                    }

                    // если надо, отправляем в разработку
                    if ($sendToDev) {

                        $attrs = array (
                            'rep' => $sendData['rep'],
                            'title' => $zdTicket->subject,
                            'body' => $ticketBody,
                            'status' => self::getIssueStatus($ticket->status),
                            'clientSource' => $this->getDriverName(),
                            'clientSourceId' => $ticket->id,
                        );

                        if ($this->getComponent()->addDevIssue($attrs)) {
                            $zdTickets = curlWrap("/tickets/".$zdTicket->id.".json", CJSON::encode(array('comment' => 'Задача передана в отдел разработки')), "PUT");
                        }
                    }
                }

                // todo: если был сделанным или закрытым, а тепер открыт, переоткрываем

                // todo: если появился новый скрытый камент, дописываем в тикет
            }

            $offset++;
            $criteria = new CDbCriteria(array(
                'limit' => $limit,
                'offset' => $offset*$limit,
            ));
            $openedTickets = ZendeskIssue::model()->inProcess()->findAll($criteria);
        }


    }


    // todo: префиксы надо задавать в параметрах
    public function getSendToDevDataFromTag ($tag)
    {
        $res = false;
        $tmp = explode('_', $tag);
        if (($tmp[1] == 'gitHub' || $tmp[1] == 'github' ) && count($tmp) == 3) {
            $res = array();
            $res['rep'] = $tmp[2];
        }
        return $res;
    }

}

// todo: параметры задавать в конфиге
define("ZDAPIKEY", "Xp4IgBmutYY0QzFp6blXLtHQRRtH4j0vANhLoRQD");
define("ZDUSER", "stenlex@gmail.com");
define("ZDURL", "https://mediasite.zendesk.com/api/v2");

/* Note: do not put a trailing slash at the end of v2 */

function curlWrap($url, $json, $action)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt($ch, CURLOPT_URL, ZDURL.$url);
	curl_setopt($ch, CURLOPT_USERPWD, ZDUSER."/token:".ZDAPIKEY);
	switch($action){
		case "POST":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			break;
		case "GET":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			break;
		case "PUT":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			break;
		case "DELETE":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;
		default:
			break;
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$output = curl_exec($ch);
	curl_close($ch);
	$decoded = json_decode($output);
	return $decoded;
}
