<?php
/**
 * SenterComponent class file.
 *
 * @author Stepanoff Alex <stenlex@gmail.com>
 */
/*
 * todo:
 * 404 заменить на exception
 */

/**
 * @package application.extensions.essentialdata
 */
class SenterComponent extends CApplicationComponent {
	
    const MAX_ISSUES = 100;

	/**
	 * @var array драйверы систем постановки задач.
	 */
	public $clientDrivers;

    /**
     * @var array драйвер системы обработки задач разработчиками.
     */
    public $devDriver;

    private $_clientDrivers = null;
    private $_devDriver = null;

	/**
	 * Returns declared client drivers
	 * @return array drivers settings.
	 */
	public function getClientDrivers() {
        if ($this->_clientDrivers === null) {
            $this->_clientDrivers = array();
            foreach ($this->clientDrivers as $driver => $options) {
                $this->_clientDrivers[strtolower($driver)] = $this->getDriverClass($options);
            }
        }
		return $this->_clientDrivers;
	}
	
	/**
	 * Returns the driver object.
	 * @param string $service the driver name.
	 * @return object the driver.
	 */
	protected function getClientDriver($driver) {
		$driver = strtolower($driver);
		$drivers = $this->getClientDrivers();
		if (!isset($drivers[$driver]))
			throw new CSenterException(500, 'Драйвер '.$driver.' не найден');
		return $drivers[$driver];
	}
	
    public function getDevDriver() {
        if ($this->_devDriver === null) {
            $opts = $this->devDriver;
            $class = $opts['class'];
            unset($opts['class']);
            $this->_devDriver = new $class();
            $this->_devDriver->init($this, $opts);
        }
        return $this->_devDriver;
    }

	/**
	 * Returns the driver class.
	 * @param string $driver the driver name.
	 * @return ISenterDriver the identity class.
	 */
	public function getDriverClass($driver) {
		$class = $driver['class'];
		$point = strrpos($class, '.');
		// if it is yii path alias
		if ($point > 0) {
			Yii::import($class);
			$class = substr($class, $point + 1);
		}
		unset($driver['class']);
		$driverClass = new $class();
		$driverClass->init($this, $driver);
		return $driverClass;
	}
	
	public function report ($message)
	{
		$data = array (
			'html' => $message,
			'text' => '',
			'subject' => Yii::app()->name . ': report',
		);
		return MailHelper::sendMailToAdmin($data);
		
	}


    public function createNewIssues ()
    {
        foreach ($this->getClientDrivers() as $driver) {
            $driver->createNewIssues ();
        }
    }

    public function createIssues ()
    {
        foreach ($this->getClientDrivers() as $driver) {
            $driver->createIssues ();
        }
    }

    public function updateOrganizations ()
    {
        foreach ($this->getClientDrivers() as $driver) {
            $items = $driver->getOrganizations();
            foreach ($items as $item) {
                $org = RequesterOrg::model()->bySource($driver->getDriverName())->byExternalId($item['externalId'])->find();
                $item['source'] = $driver->getDriverName();
                if (!$org) {
                    $org = new RequesterOrg();
                    $org->setAttributes($item);
                }
                if(!$org->save()) {
                    return false;
                }
            }
        }
        return true;
    }

    public function updateRequesters ()
    {
        foreach ($this->getClientDrivers() as $driver) {
            $items = $driver->getRequesters();
            foreach ($items as $item) {
                $org = RequesterOrg::model()->bySource($driver->getDriverName())->byExternalId($item['orgId'])->find();
                if ($org) {
                    $requester = Requester::model()->byOrgId($org->id)->byExternalId($item['externalId'])->find();
                    $item['orgId'] = $org->id;
                    if (!$requester) {
                        $requester = new Requester();
                        $requester->setAttributes($item);
                    }
                    if(!$requester->save()) {
                        return false;
                    }

                }
            }
        }
        return true;
    }

    public function markClosedIssues ()
    {
        // todo: смотреть на текущую ветку на сайте-постановщике и закрывать тикеты
    }

    public function test () {
        $devDriver = $this->getDevDriver();
        $res = $devDriver->test();
    }

    // todo: путаница в драйверах при передаче issue в markIssue
    public function markProcessIssues ()
    {
        $devDriver = $this->getDevDriver();
        $issues = $devDriver->getProcessIssues();

        if ($issues) {
            foreach ($issues as $issue) {
                $sourceIssue = Issue::model()->byDevSource($devDriver->getDriverName())->byDevId($issue->id)->find();
                if ($sourceIssue) {
                    $driver = $this->getClientDriver($sourceIssue->clientSource);
                    if ($driver->markIssue($sourceIssue, Issue::ACTION_PROCESS)) {
                        $devDriver->markIssue($issue, Issue::ACTION_PROCESS);

                        // todo: плохо, надо хотя бы bySource добавить
                        $developer = Developer::model()->byExternalId($issue->assigneeId)->find();
                        if ($developer) {
                            $sourceIssue->assigneeId = $developer->id;
                        }
                        $this->markIssue($sourceIssue, Issue::ACTION_PROCESS);
                    }
                }
            }
        }
    }


    public function markSolvedIssues ()
    {
        $devDriver = $this->getDevDriver();
        $issues = $devDriver->getSolvedIssues();

        if ($issues) {
            foreach ($issues as $issue) {
                $sourceIssue = Issue::model()->byDevSource($devDriver->getDriverName())->byDevId($issue->id)->find();
                if ($sourceIssue) {
                    $driver = $this->getClientDriver($sourceIssue->clientSource);
                    if ($driver->markIssue($sourceIssue, Issue::ACTION_SOLVED)) {
                        $devDriver->markIssue($issue, Issue::ACTION_SOLVED);
                        $this->markIssue($sourceIssue, Issue::ACTION_SOLVED);
                    }
                }
            }
        }
    }


    public function markReviewIssues ()
    {
        $devDriver = $this->getDevDriver();
        $issues = $devDriver->getReviewIssues();

        if ($issues) {
            foreach ($issues as $issue) {
                $sourceIssue = Issue::model()->byDevSource($devDriver->getDriverName())->byDevId($issue->id)->find();
                if ($sourceIssue) {
                    $driver = $this->getClientDriver($sourceIssue->clientSource);
                    if ($driver && $driver->markIssue($sourceIssue, Issue::ACTION_REVIEW)) {
                        $devDriver->markIssue($issue, Issue::ACTION_REVIEW);
                        $this->markIssue($sourceIssue, Issue::ACTION_REVIEW);
                    }
                }
            }
        }
    }


    public function uploadOpenIssuesToDev ()
    {
        foreach ($this->getClientDrivers() as $driver) {
            $driver->uploadOpenIssuesToDev ();
        }
    }

    public function addIssue ($attrs) {
        $issue = Issue::model()->byClientSource($attrs['clientSource'])->byClientId($attrs['clientSourceId'])->find();
        if (!$issue) {
            $issue = new Issue();
        }
        if ($attrs['labels']) {
            foreach ($attrs['labels'] as $label) {
                $priorityLabel = PriorityClientLabel::model()->byLabel($label)->bySource($attrs['clientSource'])->find();
                if ($priorityLabel) {
                    $priority = Priority::model()->findByPk($priorityLabel->priorityId);
                    if ($priority && $issue->priorityId != $priority->id) {
                        if ($priority->estimate)
                            $attrs['deadlineDate'] = date('Y-m-d G:i:s', time()+($priority->estimate*60*60));
                        $attrs['priority'] = $priority->number;
                        $attrs['priorityId'] = $priority->id;
                    }
                }

                $typeLabel = IssueTypeClientLabel::model()->byLabel($label)->bySource($attrs['clientSource'])->find();
                if ($typeLabel) {
                    $type = IssueType::model()->findByPk($typeLabel->issueTypeId);
                    if ($type && $issue->typeId != $type->id) {
                        $attrs['typeId'] = $type->id;
                    }
                }
            }
        }

        if ($attrs['organization']) {
            $org = RequesterOrg::model()->byExternalId($attrs['organization'])->bySource($attrs['clientSource'])->find();
            if ($org) {
                $attrs['orgId'] = $org->id;

                $requester = Requester::model()->byExternalId($attrs['requester'])->byOrgId($org->id)->find();
                if ($requester) {
                    $attrs['requesterId'] = $requester->id;
                }
                else {
                    $attrs['requesterId'] = 0;
                }
            }
        }

        $issue->setAttributes($attrs);
        if ($issue->save()) {
            return $issue;
        }
        throw new SenterException(500, 'couldn\'t save Issue');
    }

    public function addDevIssue ($attrs)
    {
        $devDriver = $this->getDevDriver();
        $issue = Issue::model()->byClientSource($attrs['clientSource'])->byClientId($attrs['clientSourceId'])->find();
        if (!$issue) {
            $issue = new Issue();
        }
        $issue->setAttributes($attrs);
        if ($issue->save()) {
            if ($issue->devSource && $issue->devSourceId) {
                return false;
            }

            $devLabels = array();
            if ($issue->typeId) {
                $type = IssueType::model()->findByPk($issue->typeId);
                if ($type) {
                    $labels = $type->getDevLabels();
                    if (isset($labels[$attrs['rep']]) && $labels[$attrs['rep']]) {
                        $devLabels[] = $labels[$attrs['rep']];
                    }
                }
            }
            if ($issue->priorityId) {
                $priority = Priority::model()->findByPk($issue->priorityId);
                if ($priority) {
                    $labels = $priority->getDevLabels();
                    if (isset($labels[$attrs['rep']]) && $labels[$attrs['rep']]) {
                        $devLabels[] = $labels[$attrs['rep']];
                    }
                }
            }

            $devAttrs = array (
                'rep' => $attrs['rep'],
                'title' => $issue->title,
                'body' => $issue->body,
                'labels' => $devLabels,
            );

            $devIssue = $devDriver->addDevIssue($devAttrs);
            if ($devIssue) {
                $issue->devSource = $devDriver->getDriverName();
                $issue->devSourceId = $devIssue->id;
                if ($issue->save())
                    return true;
            }
        }
        return false;
    }

    public function markIssue ($issue, $action)
    {
        $updateStatus = true;
        switch ($action) {
            case Issue::ACTION_SOLVED:
                $issue->status = Issue::STATUS_SOLVED;
                break;

            case Issue::ACTION_REVIEW:
                $issue->status = Issue::STATUS_REVIEW;
                break;

            case Issue::ACTION_PROCESS:
                $issue->status = Issue::STATUS_PROCESS;
                break;

            case Issue::ACTION_PRODUCTION:
                $issue->status = Issue::STATUS_PRODUCTION;
                break;

        }

        if ($updateStatus) {
            return $issue->save();
        }
        return false;

    }
	
	

}

/**
 * The SenterException exception class.
 * 
 * @author Stepanoff Alex <stenlex@gmail.com>
 * @package application.extensions.essentialdata
 * @version 1.0
 */
class SenterException extends CHttpException {}