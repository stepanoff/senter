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

  <div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar collapsed" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="brand" href="/"><?php echo Yii::app()->params['siteName']; ?></a>
        <div class="nav-collapse collapse" style="height: 0px; ">
          <ul class="nav">
              <li class="active">
                <a href="/">Главная</a>
              </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

    <div class="container">

      <?php echo $content; ?>

      <div class="footer">
        <p>&copy; Company 2013</p>
      </div>

    </div> <!-- /container -->

    <?php if (Yii::app()->getComponent('informer')) { $this->widget('VMessagesWidget', array()); } ?>

  </body>
</html>
