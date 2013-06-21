<?php
class SenterHtmlHelper
{
    public static function getIssueTypeStyle ($issue)
    {
        $res = '';
        $type = $issue->type;
        if ($type->color) {
            $res = 'background-color: #'.$type->color.';';
        }
        return $res;
    }

    public static function getIssuePriorityStyle ($issue)
    {
        $res = '';
        $priority = $issue->priorityObj;
        if ($priority->color) {
            $res = 'background-color: #'.$priority->color.';';
        }
        return $res;
    }

    public static function getMilestonePriorityStyle ($milestone)
    {
        $res = '';
        $priority = $milestone->priorityObj;
        if ($priority->color) {
            $res = 'background-color: #'.$priority->color.';';
        }
        return $res;
    }

    public static function deadline ($issue)
    {
        $time = strtotime($issue->deadlineDate);
        if (!$time)
            return '';

        $hoursLeft = ceil( ($time - time())/(60*60) );

        $class = '';
        if ($hoursLeft < 0) {
            $class = 'label-inverse';
        }
        else if ($hoursLeft < 4) {
            $class = 'label-important';
        }
        else if ($hoursLeft < 9) {
            $class = 'label-warning';
        }
        else if ($hoursLeft < 24) {
            $class = 'label-info';
        }
        else {
            $class = '';
        }

        $class = 'label '.$class;
        $text = $hoursLeft > 0 ? 'осталось '.$hoursLeft.' ч.' : 'провалено на '.(-1*$hoursLeft).' ч.';

        return CHtml::tag('span', array('class' => $class), $text);
    }

}
?>