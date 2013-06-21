<tr>
    <td width="80%" style="<?php echo SenterHtmlHelper::getMilestonePriorityStyle($item); ?>">
        <h4><?php echo $item->title; ?></h4>
        <!--div class="btn-group">
            <a target="_blank" class="btn" action="" title="Редактировать" href="<?php echo CHtml::normalizeUrl(array('/admin/adminMilestone/edit', 'id' => $item->id)); ?>"><i class="icon-pencil"></i></a>
        </div-->
        <p>
            <?php
            $devIssues = $item->issues;
            if ($devIssues) {
                foreach ($devIssues as $issue) {
                    $this->renderPartial('application.views.site.blocks.issue', array('issue' => $issue));
                }
            }

            echo SenterHtmlHelper::deadline($item->deadlineDate);
            ?>
        </p>
    </td>
    <td width="20%" style="<?php echo SenterHtmlHelper::getMilestonePriorityStyle($item); ?>">
    <?php
    // все разработчики
    ?>
    </td>
</tr>
