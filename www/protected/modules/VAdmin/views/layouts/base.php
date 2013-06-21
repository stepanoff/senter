<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Template &middot; Bootstrap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

  </head>

  <body>

    <?php
        $this->widget('VAdminMenuWidget', array('items' => array(
            array ('title' => 'Тикеты', 'link' => '/admin/adminIssue/'),
            array ('title' => 'Проекты', 'link' => '/admin/adminMilestone/'),
            array ('title' => 'Разработчики', 'link' => '/admin/adminDeveloper/'),
            array ('title' => 'Организации', 'link' => '/admin/adminOrg/'),
            array ('title' => 'Постановщики задач', 'link' => '/admin/adminRequester/'),
            array ('title' => 'Приоритеты', 'link' => '/admin/adminPriority/'),
            array ('title' => 'Типы задач', 'link' => '/admin/adminIssueType/'),
            array ('title' => 'Лейблы статусов', 'link' => '/admin/adminIssueStatusDevLabel/'),
        )));
    ?>

    <div class="container">

      <?php echo $content; ?>

      <div class="footer">
        <p>&copy; Company 2013</p>
      </div>

    </div> <!-- /container -->

    <?php if (Yii::app()->getComponent('informer')) { $this->widget('VMessagesWidget', array()); } ?>

  </body>
</html>
