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
        <h3>Open <span class="label"><?php echo count($newIssues); ?></span></h3>
        <table class="table">
            <?php
            foreach ($newIssues as $issue) {
                $this->renderPartial('application.views.site.blocks.issue', array('issue' => $issue));
            }
            ?>
        </table>

    </div>

    <div class="span6">
        <h3>Process <span class="label"><?php echo count($inProcessIssues); ?></span></h3>
        <table class="table">
            <?php
            foreach ($inProcessIssues as $issue) {
                $this->renderPartial('application.views.site.blocks.issue', array('issue' => $issue));
            }
            ?>
        </table>

        <h3>Review <span class="label"><?php echo count($onReviewIssues); ?></span></h3>
        <table class="table">
            <?php
            foreach ($onReviewIssues as $issue) {
                $this->renderPartial('application.views.site.blocks.issue', array('issue' => $issue));
            }
            ?>
        </table>

    </div>

<?php if (!$isAjax) {
    ?>
</div>

<script type="text/javascript">
    $(document).ready(function(){

        function reloadPageContent() {
            var sendObj = {
                url : "<?php echo CHtml::normalizeUrl(array('/site/index')); ?>",
                "dataType" : "json",
                "data" : {},
                "success" : function (data) { if(data["issuesList"]) {$("#issuesList").html(data["issuesList"])}  }
            };

            $.ajax(sendObj);
        }
        setInterval(reloadPageContent, 60000);
    });
</script>
    <?php
}
?>