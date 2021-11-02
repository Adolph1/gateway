<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;

\hail812\adminlte3\widgets\Alert::widget([
    'type' => 'success',
    'body' => '<h3>Congratulations!</h3>'
]);

\hail812\adminlte3\assets\FontAwesomeAsset::register($this);
\hail812\adminlte3\assets\AdminLteAsset::register($this);
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700');

$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        #loader1 {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 1;
            width: 30px;
            height: 30px;
            margin: -75px 0 0 -75px;
            border: 7px solid #e9ebee;
            border-radius: 50%;
            border-top: 7px solid #cccc;
            border-bottom: 7px solid #d8eefa;
            width: 100px;
            height: 100px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Add animation to "page content" */
        .animate-bottom {
            position: relative;
            -webkit-animation-name: animatebottom;
            -webkit-animation-duration: 1s;
            animation-name: animatebottom;
            animation-duration: 1s
        }

        @-webkit-keyframes animatebottom {
            from { bottom:-100px; opacity:0 }
            to { bottom:0px; opacity:1 }
        }

        @keyframes animatebottom {
            from{ bottom:-100px; opacity:0 }
            to{ bottom:0; opacity:1 }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<?php $this->beginBody() ?>

<div class="wrapper container-fluid" style="font-size: 0.85em">
    <!-- Navbar -->
    <?= $this->render('navbar', ['assetDir' => $assetDir]) ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <?= $this->render('sidebar', ['assetDir' => $assetDir]) ?>

    <!-- Content Wrapper. Contains page content -->
    <?= $this->render('content', ['content' => $content, 'assetDir' => $assetDir]) ?>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <?= $this->render('control-sidebar') ?>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <?= $this->render('footer') ?>
</div>
<?php
yii\bootstrap4\Modal::begin([
    'headerOptions' => ['id' => 'modalHeader'],
    'id' => 'modal',
    'size' => 'modal-lg',
    //keeps from closing modal with esc key or by clicking out of the modal.
    // user must click cancel or X to close
    //'clientOptions' => ['backdrop' => 'static', 'keyboard' => false]
]);
echo "<div id='modalContent'></div>";
yii\bootstrap4\Modal::end();
?>
<?php $this->endBody() ?>
</body>
</html>
<script>
    $("#source-id").change(function(){
        document.getElementById("loader1").style.display = "block";

        setTimeout(loadDestinations, 500);


    });

    function loadDestinations() {
        var id = document.getElementById("source-id").value;

       // alert(id);
        $.get("<?php echo Yii::$app->urlManager->createUrl(['country/load-all', 'id' => '']);?>" + id, function (data) {
          //  alert(data);

            document.getElementById("dest-ids").innerHTML = data;
            document.getElementById("loader1").style.display = "none";



        });




    }


    $("#preview-airway-bill").click(function(){
        // alert('yes bro');
        var printContents = document.getElementById('airway-bill-label').innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
    });



    //creates supplier by ajax
    $("#supplier-id").change(function(){
        var id = document.getElementById('supplier-id');
        // alert(id);
        var nam = id.options[id.selectedIndex].text;
        if(nam == 'Create new'){
            //check if the modal is open. if it's open just reload content not whole modal
            //also this allows you to nest buttons inside of modals to reload the content it is in
            //the if else are intentionally separated instead of put into a function to get the
            //button since it is using a class not an #id so there are many of them and we need
            //to ensure we get the right button and content.
            if ($('#modal').data('bs.modal').isShown) {
                $('#modal').find('#modalContent')
                    .load("<?php echo Yii::$app->urlManager->createUrl(['supplier/create-new','id' => '']);?>");
                //dynamiclly set the header for the modal via title tag
                document.getElementById('modalHeader').innerHTML = '<h4>New Supplier Form</h4>';
            } else {
                //if modal isn't open; open it and load content
                $('#modal').modal('show')
                    .find('#modalContent')
                    .load("<?php echo Yii::$app->urlManager->createUrl(['supplier/create-new','id' => ''])?>");
                //dynamiclly set the header for the modal via title tag
                document.getElementById('modalHeader').innerHTML = '<h4>New Supplier Form</h4>';
            }
        }
    });

</script>
<?php $this->endPage() ?>


