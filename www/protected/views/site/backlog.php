<?php if (!$isAjax) {
    ?>
<style type="text/css">
    .issues {
        __height: 700px;
        __overflow-y: scroll;
    }
    .issues td {
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
        color: rgb(90, 90, 90);
    }
</style>
<div class="row-fluid issues" id="issuesList">
    <?php
}
?>

    <div class="span6">
        <h3>Отложенные тикеты <span class="label"><?php echo count($issues); ?></span></h3>
        <table class="table">
            <?php
            foreach ($issues as $issue) {
                $this->renderPartial('application.views.site.blocks.issue', array('issue' => $issue));
            }
            ?>
        </table>

    </div>

<?php if (!$isAjax) {
    ?>
</div>
    <?php
}
?>