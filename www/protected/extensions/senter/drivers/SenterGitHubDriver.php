<?php
/*
 * todo: написать базовый класс
 */
class SenterGithubDriver extends CComponent {
	
	protected $name = 'github';
	protected $title = 'GitHub';
	protected $attributes = array();

    protected $issueModel = 'GitHubIssue';

    protected $login;
    protected $password;
    protected $githubUser; // todo: добавить поддержку работы с репозиториями нескольких пользователей
    protected $repos = array('gpor', 'old', 'auth-backend', 'chef');

    private $_client = null;

    public function getModelName () {
        return $this->issueModel;
    }

    public function init($component, $options = array()) {
        if (isset($component))
            $this->setComponent($component);

        foreach ($options as $key => $val)
            $this->$key = $val;

        if (!$this->login || !$this->password) {
            throw new SenterException(500, 'Не заданы логин или пароль');
        }
        $this->_client = new Github\Client();
        $this->_client->authenticate($this->login, $this->password, 'http_password');
    }


    public function statusTypes ($source = false)
    {
        switch ($source) {
            case 'Issue':
                return array (
                    GitHubIssue::STATUS_NEW => Issue::STATUS_NEW,
                    GitHubIssue::STATUS_OPEN => Issue::STATUS_PROCESS,
                    GitHubIssue::STATUS_REVIEW => Issue::STATUS_REVIEW,
                    GitHubIssue::STATUS_CLOSED => Issue::STATUS_SOLVED,
                    GitHubIssue::STATUS_PRODUCTION => Issue::STATUS_PRODUCTION,
                );
                break;

            default:
                return array (
                    GitHubIssue::STATUS_NEW => 'open',
                    GitHubIssue::STATUS_OPEN => 'open',
                    GitHubIssue::STATUS_REVIEW => 'open',
                    GitHubIssue::STATUS_CLOSED => 'closed',
                    GitHubIssue::STATUS_PRODUCTION => 'closed',
                );
                break;
        }
    }

    /**
     * Returns driver name(id).
     * @return string the driver name(id).
     */
    public function getDriverName() {
        return $this->name;
    }

    /**
     * Returns driver title.
     * @return string the driver title.
     */
    public function getDriverTitle() {
        return $this->title;
    }

    /**
     * Sets {@link SenterComponent} application component
     * @param SenterComponent $component the application component.
     */
    public function setComponent($component) {
        $this->component = $component;
    }

    /**
     * Returns the {@link SenterComponent} application component.
     * @return SenterComponent the {@link EssentialDataServiceBase} application component.
     */
    public function getComponent() {
        return $this->component;
    }

    public function getRepos()
    {
        return $this->repos;
    }

    public function getRepOwner ()
    {
        return $this->githubUser;
    }

    public function test () {
        //$this->_client->api('issue')->labels()->replace($this->githubUser, 'gpor', 1980, array('bug') );
        //$repo   = $this->_client->getHttpClient()->put('repos/mediasite/gpor/issues/1980/labels', array(1=>'bug'));
        //$res = $this->_client->getHttpClient()->get('repos/mediasite/old/issues/1046/events');

        $res  = $this->_client->getHttpClient()->get('repos/mediasite/gpor/issues');
        //$res = $this->_client->getHttpClient()->get('repos/mediasite/gpor/pulls', array('state' => 'closed'));
        //$res = $this->_client->getHttpClient()->get('repos/mediasite/old/pulls/1051');
        //$res = $this->_client->getHttpClient()->get('repos/mediasite/gpor/commits/fe4479c4dd10eecb6c4b28fabc0bcd36bb9494e4');
        print_r($res->getContent());
        die();

        //$this->synchronizeIssues();
        die();
    }

    public function getSolvedIssues ()
    {
        $res = GitHubIssue::model()->isSolved()->findAll();
        return $res;
    }

    public function getReviewIssues ()
    {
        $res = GitHubIssue::model()->onReview()->findAll();
        return $res;
    }

    public function getProcessIssues ()
    {
        $res = GitHubIssue::model()->inProcess()->findAll();
        return $res;
    }

    public function getNotClosedIssues ()
    {
        $res = GitHubIssue::model()->notClosed()->findAll();
        return $res;
    }


    public function synchronizeIssues ()
    {
        $this->createNewIssues();
        $this->createNewMilestones();

        $lastCommits = array();
        foreach ($this->repos as $repo) {
            $lastCommits[$repo] = array();
            $tmp = $this->_client->getHttpClient()->get('repos/'.$this->githubUser.'/'.$repo.'/commits');
            if ($tmp)
                $lastCommits[$repo] = $tmp->getContent();
        }

        $pullRequests = array();
        foreach ($this->repos as $repo) {
            $pullRequests[$repo] = array();
            $tmp = $this->_client->getHttpClient()->get('repos/'.$this->githubUser.'/'.$repo.'/pulls', array('state' => 'closed'));
            if ($tmp)
                $pullRequests[$repo] = array_merge($pullRequests[$repo], $tmp->getContent());

            $tmp = $this->_client->getHttpClient()->get('repos/'.$this->githubUser.'/'.$repo.'/pulls', array('state' => 'open'));
            if ($tmp)
                $pullRequests[$repo] = array_merge($pullRequests[$repo], $tmp->getContent());
        }

        $issues = GitHubIssue::model()->notClosed()->findAll();
        foreach ($issues as $issue) {
            $githubTask = $this->_client->api('issue')->show($this->githubUser, $issue->rep, $issue->repNum);
            if (!$githubTask)
                continue;

            $issue->status = GitHubIssue::STATUS_NEW;

            $issue->milestoneId = 0;
            // записываем milestone
            if ($githubTask['milestone']) {
                $mAttrs = $githubTask['milestone'];
                $mAttrs['rep'] = $issue->rep;
                $milestone = $this->getMilestone($mAttrs);
                $issue->milestoneId = $milestone->id;
            }

            // ставим assignee
            if ($githubTask['assignee'] && $githubTask['assignee']['id']) {
                $issue->assigneeId = $githubTask['assignee']['id'];
                $issue->status = GitHubIssue::STATUS_OPEN;
                $developer = $this->getDeveloper($githubTask['assignee']); // добавляем нового разработчика при необходимости
            }
            else {
                $issue->assigneeId = '';
            }

            // ставим статус тикету по тегу
            $statusFound = false;
            $labels = array();
            if ($githubTask['labels']) {
                foreach ($githubTask['labels'] as $l) {
                    $labels[] = $l['name'];
                }
                foreach ($labels as $label) {
                    $issueLabel = IssueStatusDevLabel::model()->bySource($this->getDriverName())->byLabel($label)->byRep($issue->rep)->find();
                    if ($issueLabel) {
                        $issue->status = $issueLabel->issueStatusId;
                        $statusFound = true;
                    }
                }
            }


            // если не получилось, пытаемся определять статус автоматически
            if (!$statusFound) {
                $res = $this->_client->getHttpClient()->get('repos/'.$this->githubUser.'/'.$issue->rep.'/issues/'.$githubTask['number'].'/events');
                $issueEvents = array();
                if ($res)
                    $issueEvents = $res->getContent();
                foreach ($issueEvents as $issueEvent) {
                    if ($issueEvent['event'] == 'referenced' || $issueEvent['event'] == 'closed' || $issueEvent['event'] == 'merged') {
                        $pullRequest = self::findPullRequestByCommitSha($issueEvent['commit_id'], $pullRequests[$issue->rep]);
                        if ($pullRequest) {
                            $issue->addPullRequest($pullRequest);
                            $issue->addLastEvent('pullRequest', $issueEvent);
                        }
                    }
                    if ($issueEvent['event'] == 'closed') {
                        $issue->addLastEvent('closed', $issueEvent);
                    }
                    if ($issueEvent['event'] == 'reopened') {
                        $issue->addLastEvent('reopened', $issueEvent);
                    }
                }

                $lastPullRequestEvent = $issue->getLastEvent('pullRequest');
                $lastPullRequest =  $issue->getPullRequest();
                $pullRequestDate = $lastPullRequestEvent ? strtotime($lastPullRequestEvent['created_at']) : 0;

                $lastClosed = $issue->getLastEvent('closed');
                $closedDate = $lastClosed ? strtotime($lastClosed['created_at']) : 0;

                $lastReopened = $issue->getLastEvent('reopened');
                $reopenedDate = $lastReopened ? strtotime($lastReopened['created_at']) : 0;

                if ($pullRequestDate && $pullRequestDate >= $reopenedDate && !$lastPullRequest['merged_at']) {
                    if ($lastPullRequest && $lastPullRequest['base']['ref'] == 'master') {
                        $issue->masterCommitSha = $lastPullRequest['base']['sha'];
                        $issue->status = GitHubIssue::STATUS_REVIEW;
                    }
                }
                if ($githubTask['state'] == 'closed' && $closedDate && $closedDate > $reopenedDate && $closedDate >= $pullRequestDate) {
                    if ($lastPullRequest && $lastPullRequest['merged_at']) {
                        $issue->status = GitHubIssue::STATUS_CLOSED;
                        if ($lastPullRequest['base']['ref'] == 'master') {
                            $issue->masterCommitSha = $lastPullRequest['base']['sha'];
                        }
                    }
                    if (!$lastPullRequest) {
                        $issue->status = GitHubIssue::STATUS_CLOSED;
                    }
                }
            }

            // ставим дату закрытия
            if ($issue->status == GitHubIssue::STATUS_CLOSED) {
                $issue->mergedAt = date('Y-m-d G:i:s', strtotime($lastPullRequest['merged_at']));
            }

            $issue->save();

        }

    }

    public function createNewIssues ()
    {
        foreach ($this->repos as $repo) {
            $issues = array();
            $openedIssues = array();
            $res = $this->_client->getHttpClient()->get('repos/'.$this->githubUser.'/'.$repo.'/issues', array('sort' => 'created', 'direction' => 'desc', 'state' => 'open' ));
            if ($res) {
                $openedIssues = $res->getContent();
            }

            $closedIssues = array();
            $res = $this->_client->getHttpClient()->get('repos/'.$this->githubUser.'/'.$repo.'/issues', array('sort' => 'created', 'direction' => 'desc', 'state' => 'closed' ));
            if ($res) {
                $closedIssues = $res->getContent();
            }

            $issues = array_merge($openedIssues, $closedIssues);

            foreach ($issues as $githubIssue) {
                // пропускаем пулл-реквесты
                if ($githubIssue['pull_request'] && !empty($githubIssue['pull_request']['html_url'])) {
                    continue;
                }

                $issue = GitHubIssue::model()->byRep($repo)->byRepNum($githubIssue['number'])->find();
                if (!$issue) {
                    $issue = new GitHubIssue();
                    $issue->rep = $repo;
                    $issue->repNum = $githubIssue['number'];
                    $issue->status = GitHubIssue::STATUS_NEW;
                    if ($issue->save()) {
                        $labels = array();
                        if ($githubIssue['labels']) {
                            foreach ($githubIssue['labels'] as $l) {
                                $labels[] = $l['name'];
                            }
                        }
                        $attrs = array (
                            'title' => $githubIssue['title'],
                            'body' => $githubIssue['body'],
                            'labels' => $labels,
                            'devSource' => $this->getDriverName(),
                            'devSourceId' => $issue->id,
                            'rep' => $repo,
                        );
                        $this->getComponent()->addIssueFromDev($attrs);
                    }

                }
                else {
                    // переоткрытые тикеты
                    $currentStatus = $githubIssue['state'];
                    if ($issue->status >= GitHubIssue::STATUS_OPEN && $currentStatus == 'open') {
                        $issue->status = GitHubIssue::STATUS_NEW;
                        $issue->save();
                    }
                }
            }
        }
    }

    public function createNewMilestones ()
    {
        foreach ($this->repos as $repo) {
            $milestones = array();
            $res = $this->_client->getHttpClient()->get('repos/'.$this->githubUser.'/'.$repo.'/milestones', array('state' => 'open' ));
            if ($res) {
                $milestones = $res->getContent();
                foreach ($milestones as $attrs) {
                    $attrs['rep'] = $repo;
                    $milestone = $this->getMilestone($attrs);
                }
            }
        }
    }


    public function markIssue ($issue, $status)
    {
        switch ($status) {
            case Issue::ACTION_SOLVED:
                $issue->status = GitHubIssue::STATUS_CLOSED;
                if ($issue->save())
                    return true;
                break;

            case Issue::ACTION_REVIEW:
                $issue->status = GitHubIssue::STATUS_REVIEW;
                if ($issue->save())
                    return true;
                break;

            case Issue::ACTION_PROCESS:
                $issue->status = GitHubIssue::STATUS_OPEN;
                if ($issue->save())
                    return true;
                break;

            case Issue::ACTION_REOPEN:
                $issue->status = GitHubIssue::STATUS_OPEN;
                // todo: поменять статус на гитхабе
                if ($issue->save())
                    return true;
                break;
        }
        return false;
    }


    public function sendIssueToDev ($attrs)
    {
        $res = $this->_client->api('issue')->create($this->githubUser, $attrs['rep'], array(
            'title' => $attrs['title'],
            'body' => $attrs['body'],
        ));
        if ($res && is_array($res) && $res['id']) {

            if ($attrs['labels']) {
                $labels = array();
                foreach ($attrs['labels'] as $label) {
                    $labels[] = array('name' => $label);
                }
                //$this->_client->api('issue')->labels()->replace($this->githubUser, $attrs['rep'], $res['number'], $labels);
            }

            $task = new GitHubIssue();
            $task->rep = $attrs['rep'];
            $task->repNum = $res['number'];
            $task->status = GitHubIssue::STATUS_NEW;
            if ($task->save())
                return $task;
        }
        return false;
    }

    public function getIssueById ($id)
    {
        $modelName = $this->issueModel;
        return $modelName::model()->findByPk($id);
    }

    public function getMilestoneById ($id)
    {
        return GitHubMilestone::model()->findByPk($id);
    }

    public static function findPullRequestByCommitSha ($sha, $pulls)
    {
        $res = false;
        foreach ($pulls as $pull) {
            if ($pull['head']['sha'] == $sha) {
                $res = $pull;
                break;
            }
        }
        return $res;
    }

    public function removeDevIssueByIssue ($issue)
    {
        $model = GitHubIssue::model()->findByPk($issue->devSourceId);
        if ($model) {
            return $model->delete();
        }
    }

    public function getCollaborators ($issue) {
        $res = $this->_client->getHttpClient()->get('repos/'.$this->githubUser.'/'.$issue->rep.'/issues/'.$issue->repNum.'/events');
        $issueEvents = array();
        if ($res)
            $issueEvents = $res->getContent();

        $executorId = false;
        $reviewerId = false;
        $collaborators = array();
        foreach ($issueEvents as $issueEvent) {
            switch ($issueEvent['event']) {
                case 'referenced':
                    if ($issueEvent['commit_id'] && !$executorId) {
                        $executorId = $issueEvent['actor']['id'];
                    }
                break;

                case 'closed':
                    if (!$executorId) {
                        $executorId = $issueEvent['actor']['id'];
                    }
                break;

                case 'merged':
                    $reviewerId = $issueEvent['actor']['id'];
                break;

            }
            if (!isset($collaborators[$issueEvent['actor']['id']])) {
                $collaborators[$issueEvent['actor']['id']] = $issueEvent['actor'];
                $collaborators[$issueEvent['actor']['id']]['type'] = IssueCollaborator::TYPE_OTHER;
            }
        }

        if ($executorId) {
            $collaborators[$executorId]['type'] = IssueCollaborator::TYPE_EXECUTOR;
        }
        if ($reviewerId) {
            $collaborators[$reviewerId]['type'] = IssueCollaborator::TYPE_REVIEWER;
        }

        $res = array();
        foreach ($collaborators as $collaborator) {
            $developer = $this->getDeveloper($collaborator);

            if (!$developer)
                continue;

            $res[] = array(
                'developerId' => $developer->id,
                'collaborationType' => $collaborator['type'],
            );
        }
        return $res;

    }

    public function getDeveloper ($attrs)
    {
        $developer = Developer::model()->byExternalId($attrs['id'])->bySource($this->getDriverName())->find();
        if (!$developer) {
            $developer = new Developer();
            $developer->externalId = $attrs['id'];
            $developer->username = $attrs['login'];
            $developer->avatarUrl = $attrs['avatar_url'];
            $developer->url = $attrs['url'];
            $developer->source = $this->getDriverName();
            $developer->save();
        }
        return $developer;
    }

    public function getMilestone ($attrs)
    {
        $githubMilestone = GitHubMilestone::model()->byRep($attrs['rep'])->byRepNum($attrs['number'])->find();
        $milestone = false;
        if (!$githubMilestone) {
            $githubMilestone = new GitHubMilestone();
            $githubMilestone->rep = $attrs['rep'];
            $githubMilestone->repNum = $attrs['number'];
            $githubMilestone->save();
        }
        else {
            $milestone = Milestone::model()->byDevSource($this->getDriverName())->byDevId($githubMilestone->id)->find();
        }

        if ($githubMilestone && !$milestone) {
            $milestone = new Milestone();
            $milestone->devSource = $this->getDriverName();
            $milestone->devSourceId = $githubMilestone->id;
            $milestone->title = $attrs['title'];
            $milestone->body = $attrs['description'];
            $milestone->status = Milestone::STATUS_NEW;
            $milestone->save();
        }

        return $githubMilestone;
    }

    public function convertStatus ($status, $source = false) {
        $statuses = $this->statusTypes($source);
        foreach ($statuses as $k=>$v) {
            if ($v == $status)
                return $k;
        }
        return false;
    }

}