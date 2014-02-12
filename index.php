
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

    // POST送信された場合
    if ( $_SERVER["REQUEST_METHOD"] == "POST" ){
        switch($_POST["process_type"]){
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
    <div class="container">
        <h4>
            <?php echo $textFile->result; ?>
        </h4>
        <h2>sample.txtのファイル内容</h2>

        <!-- ファイル内容のテーブルを表示 -->
        <?php
            $textFile->viewFileValue(); 
        ?>
        <!-- End ファイル内容のテーブルを表示 -->

        <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#formModal" onClick="doChangeRegistForm();">
          登録
        </button>

        <div class="modal fade" id="formModal">
          <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php" method="POST" role="form" enctype="multipart/form-data">
                <div class="modal-header" style="text-align: center;">
                    <h4 class="modal-title"><span id="formModalTitle">Modal title</span></h4>
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
                    <div class="form-group">
                        <label for="画像">画像</label>
                         <input type="hidden" name="MAX_FILE_SIZE" value="200000" />
                        <input name="image_file" type="file"/>
                    </div>
                    <div class="form-group">
                        <label for="画像の表示位置">画像の表示位置</label><br>
                        <select name="image_pos">
                            <option value="<?php echo TextFile::IMAGE_POS_TOP ?>">文章の上に画像を表示</option>
                            <option value="<?php echo TextFile::IMAGE_POS_BOTTOM ?>">文章の下に画像を表示</option>
                        </select>
                    </div>
                    <input type="hidden" name="pre_date" value="">
                    <input type="hidden" name="pre_message" value="">
                    <input type="hidden" name="pre_image_pos" value="">
                    <input type="hidden" name="pre_image_name" value="">
                    <input type="hidden" name="process_type" value="">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-default" id="regist" onClick="return doRegist();">登録</button>
                    <button type="submit" class="btn btn-default" id="update" onClick="return doUpdate();">更新</button>
                    <button type="submit" class="btn btn-default" id="delete" onClick="return doDelete();">削除</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
                </div>
                </form>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </div>
    <script type='text/javascript'>
        function doRegist(){
            if( !errorCheck() ) return false; 
            document.forms[0].elements['process_type'].value = 'regist';
            doSubmit();
            return true;
        }
        function doUpdate(){
            if( !errorCheck() ) return false; 
            document.forms[0].elements['process_type'].value = 'update';
            doSubmit();
            return true;   
        }
        function doDelete(){
            if( !errorCheck() ) return false; 
            document.forms[0].elements['process_type'].value = 'delete';
            doSubmit(); 
            return true;
        }
        function doSubmit(){
            document.forms[0].submit();
        }

        function errorCheck(){
            var date = document.forms[0].elements['date'].value;
            if(date == ''){
                alert("日付が未入力です。");
                return false;
            }
            if( !date.match(/[0-9]{4}\/[0-9]{2}\/[0-9]{2}/) ){
                alert("日付の形式が間違っています。 (ex 2000/01/01)");
                return false;
            }
            return true;
        }
        function doChangeRegistForm(){
            document.getElementById( "formModalTitle" ).innerHTML = "登録画面";
            document.getElementById( "regist" ).style.display = 'inline-block';
            document.getElementById( "update" ).style.display = 'none';
            document.getElementById( "delete" ).style.display = 'none';

            // フォームの内容を初期化。
            document.forms[0].reset();
        }
        function doChangeUpdateForm(date, message, image_name, image_pos){
            document.getElementById( "formModalTitle" ).innerHTML = "更新・削除画面";
            document.getElementById( "regist" ).style.display = 'none';
            document.getElementById( "update" ).style.display = 'inline-block';
            document.getElementById( "delete" ).style.display = 'inline-block';

            document.forms[0].elements['date'].value    = date;
            document.forms[0].elements['message'].value = message;
            switch(image_pos){
                case 0:
                    document.forms[0].elements['image_pos'].selectedIndex = 0;
                    break;
                case 1:
                    document.forms[0].elements['image_pos'].selectedIndex = 1;
                    break;
            }

            document.forms[0].elements['pre_date'].value    = date;
            document.forms[0].elements['pre_message'].value = message;
            document.forms[0].elements['pre_image_name'].value = image_name;
            document.forms[0].elements['pre_image_pos'].value = image_pos;
        }
    </script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
</body>

</html>