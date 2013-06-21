<tr>
    <td width="20%" style="<?php echo SenterHtmlHelper::getIssueTypeStyle($issue); ?>"><h1><?php echo $issue->devIssue->number; ?></h1></td>
    <td width="60%" style="<?php echo SenterHtmlHelper::getIssuePriorityStyle($issue); ?>">
        <h4><?php echo $issue->title; ?></h4>
        <!--div class="btn-group">
            <a target="_blank" class="btn" action="" title="Редактировать" href="<?php echo CHtml::normalizeUrl(array('/admin/adminIssue/edit', 'id' => $issue->id)); ?>"><i class="icon-pencil"></i></a>
        </div-->
        <p>
            <?php
            $devIssue = $issue->devIssue;
            if ($devIssue) {
                echo '<span>'.CHtml::link($devIssue->rep, $devIssue->getUrl()).'</span> ';
            }

            $clientIssue = $issue->clientIssue;
            if ($clientIssue) {
                echo '<span>'.CHtml::link('zendesk', $clientIssue->getUrl()).'</span> ';
            }

            echo SenterHtmlHelper::deadline($issue->deadlineDate);
            ?>
        </p>
    </td>
    <td width="20%" style="<?php echo SenterHtmlHelper::getIssuePriorityStyle($issue); ?>">
    <?php
    if ($issue->developer) {
        ?>
        <img src="<?php echo $issue->developer->avatarUrl; ?>" class="img-circle">
        <?php
    }
    ?>
    </td>
</tr>
