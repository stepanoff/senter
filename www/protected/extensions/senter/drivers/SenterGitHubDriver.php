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
    protected $repos = array('gpor', 'old');

    private $_client = null;

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


    public function statusTypes ()
    {
       return array (
           Issue::STATUS_NEW => 'open',
           Issue::STATUS_OPEN => 'open',
           Issue::STATUS_PENDING => 'open',
           Issue::STATUS_HOLD => 'open',
           Issue::STATUS_SOLVED => 'closed',
           Issue::STATUS_CLOSED => 'closed',
       );
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

    public function test () {
        //$this->_client->api('issue')->labels()->replace($this->githubUser, 'gpor', 1980, array('bug') );
        $repo   = $this->_client->getHttpClient()->put('repos/mediasite/gpor/issues/1980/labels', array(1=>'bug'));
        die();
    }

    public function getSolvedIssues ()
    {
        $res = array();
        $reviewTasks = GitHubIssue::model()->onReview()->findAll();
        foreach ($reviewTasks as $reviewTask) {
            $pullRequest = $this->_client->api('pull_request')->show($this->githubUser, $reviewTask->rep, $reviewTask->pullRequestNum);
            if ($pullRequest) {
                if ($pullRequest['state'] == 'closed' && $pullRequest['merged']) {
                    $reviewTask->addPullRequest($pullRequest);
                    $res[] = $reviewTask;
                }
            }
        }
        return $res;
    }

    public function getReviewIssues ()
    {
        $res = array();

        $allOpenPullRequests = array();
        foreach ($this->repos as $repo) {
            $allOpenPullRequests[$repo] = $this->_client->api('pull_request')->all($this->githubUser, $repo, 'open');
        }
        foreach ($allOpenPullRequests as $repo => $openPullRequests)
        {
            foreach ($openPullRequests as $openPullRequest) {
                if (preg_match_all('#\#([\d]+)#', $openPullRequest['body'], $matches)) {
                    $taskNum = $matches[1][0];
                    $task = GitHubIssue::model()->byRep($repo)->byRepNum($taskNum)->find();
                    if ($task && $task->status < GitHubIssue::STATUS_REVIEW) {
                        $task->addPullRequest($openPullRequest);
                        $res[] = $task;
                    }
                }
            }
        }
        return $res;
    }

    public function getProcessIssues ()
    {
        $res = array();

        $allIssues = array();
        foreach ($this->repos as $repo) {
            $allIssues[$repo] = GitHubIssue::model()->byRep($repo)->unassigned()->findAll();
        }
        foreach ($allIssues as $repo => $issues) {
            foreach ($issues as $issue) {
                $unassignedTask = $this->_client->api('issue')->show($this->githubUser, $repo, $issue->repNum);
                if ($unassignedTask) {
                    if ($unassignedTask['assignee'] && $unassignedTask['assignee']['id']) {
                        if ($issue->isOpen()) {
                            $issue->assigneeId = $unassignedTask['assignee']['id'];
                            $res[] = $issue;
                        }

                        $developer = Developer::model()->byExternalId($unassignedTask['assignee']['id'])->find();
                        if (!$developer) {
                            $developer = new Developer();
                            $developer->externalId = $unassignedTask['assignee']['id'];
                            $developer->username = $unassignedTask['assignee']['login'];
                            $developer->avatarUrl = $unassignedTask['assignee']['avatar_url'];
                            $developer->url = $unassignedTask['assignee']['url'];
                            $developer->save();
                        }
                    }
                }
            }
        }

        return $res;

    }


    public function markIssue ($issue, $status)
    {
        switch ($status) {
            case Issue::ACTION_SOLVED:
                $pull = $issue->getPullRequest();
                if ($pull)
                    $issue->masterCommitSha = $pull['head']['sha'];
                $issue->status = GitHubIssue::STATUS_CLOSED;
                if ($issue->save())
                    return true;
                break;

            case Issue::ACTION_REVIEW:
                $pull = $issue->getPullRequest();
                if ($pull)
                    $issue->pullRequestNum = $pull['number'];
                $issue->status = GitHubIssue::STATUS_REVIEW;
                if ($issue->save())
                    return true;
                break;

            case Issue::ACTION_PROCESS:
                $issue->status = GitHubIssue::STATUS_OPEN;
                if ($issue->save())
                    return true;
                break;
        }
        return false;
    }


    public function addDevIssue ($attrs)
    {
        $res = $this->_client->api('issue')->create($this->githubUser, $attrs['rep'], array(
            'title' => $attrs['title'],
            'body' => $attrs['body'],
        ));
        if ($res && is_array($res) && $res['id']) {

            if ($attrs['labels']) {
                $this->_client->api('issue')->labels()->replace($this->githubUser, $attrs['rep'], $res['number'], $attrs['labels']);
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


}