
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>test</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap-theme.min.css">

    <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.9.1.js"></script>
    <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/i18n/jquery-ui-i18n.min.js"></script>
    <link rel="stylesheet" href="/resources/demos/style.css">
    <script>
        $(function() {
            $.datepicker.setDefaults( $.datepicker.regional[ "ja" ] );
            $( "#datepicker" ).datepicker();
        });
    </script>


</head>


<body>
<?php
    ini_set('display_errors', 1);
    require_once('./TextFile.php');

    // テキストファイルクラスの作成
    $textFile = new TextFile();
?>
    <div class="container">
        <?php
            
            if ( $_SERVER["REQUEST_METHOD"] == "POST" ){
                switch($_POST["PROCESS_TYPE"]){
                    case 'regist':
                        $textFile->regist();
                        break;
                    case 'update':
                        $textFile->update();
                        break;
                    case 'delete':
                        $textFile->delete();
                        break;            
                }
            }
        ?>
        <h2>sample.txtのファイル内容</h2>

        <?php
            $textFile->viewFileValue(); 
        ?>

        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#formModal" onClick="doChangeRegistForm();">
          登録
        </button>

        <div class="modal fade" id="formModal">
          <div class="modal-dialog">
            <div class="modal-content">
                <form action="test.php" method="POST" role="form">
                <div class="modal-header">
                    <h4 class="modal-title">Modal title</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="日付">日付</label>
                        <input type="text" id="datepicker" name="date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="メッセージ欄">メッセージ欄</label>
                        <textarea name="message" rows="4" cols="40" class="form-control">メッセージ欄</textarea>
                    </div>
                    <input type="hidden" name="pre_date" value="">
                    <input type="hidden" name="pre_message" value="">
                    <input type="hidden" name="PROCESS_TYPE" value="">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-default" id="regist" onClick="doRegist();">登録</button>
                    <button type="submit" class="btn btn-default" id="update" onClick="doUpdate();">更新</button>
                    <button type="submit" class="btn btn-default" id="delete" onClick="doDelete();">削除</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                </div>
                </form>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </div>
    <script type='text/javascript'>
        function doRegist(){
            document.forms[0].elements['PROCESS_TYPE'].value = 'regist';
            doSubmit();
        }
        function doUpdate(){
            document.forms[0].elements['PROCESS_TYPE'].value = 'update';
            doSubmit();    
        }
        function doDelete(){
            document.forms[0].elements['PROCESS_TYPE'].value = 'delete';
            doSubmit(); 
        }            
        function doSubmit(){
            document.forms[0].submit();
        }

        function doChangeRegistForm(){
            document.getElementById( "regist" ).style.display = 'inline-block';
            document.getElementById( "update" ).style.display = 'none';
            document.getElementById( "delete" ).style.display = 'none';
        }
        function doChangeUpdateForm(date, message){
            document.getElementById( "regist" ).style.display = 'none';
            document.getElementById( "update" ).style.display = 'inline-block';
            document.getElementById( "delete" ).style.display = 'inline-block';

            document.forms[0].elements['date'].value    = date;
            document.forms[0].elements['message'].value = message;

            document.forms[0].elements['pre_date'].value    = date;
            document.forms[0].elements['pre_message'].value = message;
        }
    </script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
</body>

</html>