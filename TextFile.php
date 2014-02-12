<?php

class TextFile{
    // 入力された日付
    protected $date = '';
    // 入力されたメッセージ
    protected $message = '';
    // 画像名
    protected $imageName = '';
    // 入力された画像の表示位置
    protected $imagePos = 0;

    // 処理結果
    public $result = '';

    // タグのエスケープ判断フラグ
    // protected $tagEscapeFlg = false;


//-------------
//    定数
//-------------
    // 編集するファイルパス
    const FILE_PATH = "./sample.txt";
    // ファイルアップロード時の保存パス
    const FILE_SAVE_PATH = "/var/www/html/";

    // ファイルオープン関数の　引数一覧
    const FILE_READ_MODE  = 0;  // 読み取り専用の場合
    const FILE_WRITE_MODE = 1;  // 書き込み専用の場合
    const FILE_ADD_MODE   = 2;  // 追加専用の場合

    // 画像表示位置の 値と位置の関係
    const IMAGE_POS_TOP    = 0;  // 文章の上に表示
    const IMAGE_POS_BOTTOM = 1;  // 文章の下に表示

    // 書き込みタグ文字列 日付,画像,文章の場合
    const WRITE_TAG_STR1 = '<span class="date">%s</span><br><img src="%s" width="200px"><div>%s</div>';
    const WRITE_TAG_STR1_PATTERN = '/<span class="date">.*<\/span><br><img src=".*" width="200px"><div>.*<\/div>/';

    // 書き込みタグ文字列 日付,文章,画像の場合
    const WRITE_TAG_STR2 = '<span class="date">%s</span><div>%s</div><img src="%s" width="200px">';
    const WRITE_TAG_STR2_PATTERN = '/<span class="date">.*<\/span><div>.*<\/div><img src=".*" width="200px">/';

    // ファイルアップロード時に、許可する拡張子
    private $errorChkExtension = array("gif","jpg", "jpeg", "png", "bmp");
//--------------



    //---------------
    //  登録メソッド
    //---------------
    public function regist(){

        // ファイルオープン と　ロック
        $fp = $this->fileOpenAndLock(TextFile::FILE_ADD_MODE);
        if(!$fp) return false;

        //--------------
        //  ファイル登録
        //--------------
        $this->date    = $_POST["date"];
        // メッセージ内の改行を<br>タグに置き換える
        $this->message = str_replace(array("\r\n","\r","\n"), "<br>", $_POST["message"]);

        // ファイルアップロード
        if( !$this->fileUpload() ) return false;

        switch($_POST["image_pos"]){
            case TextFile::IMAGE_POS_TOP:
                $data = sprintf(TextFile::WRITE_TAG_STR1,$this->date, $this->imageName, $this->message);
                break;
            case TextFile::IMAGE_POS_BOTTOM:
                $data = sprintf(TextFile::WRITE_TAG_STR2,$this->date, $this->message, $this->imageName);
                break;
        }

        $data = "\n" . $data;
        fwrite($fp,  $data);
        //----------------------------------

        // ファイルクローズ と アンロック
        if( $this->fileCloseAndUnlock($fp) ) return false;

        $this->result = "ファイルの登録に成功しました";
        return true;
    }


    //-------------------------
    //  ファイル内容表示メソッド
    //-------------------------
    public function viewFileValue(){

        // ファイル内容を改行コードごとに、Arrayへ設定する
        $fileArray = $this->getFileValueArray();
        if( !$fileArray ) return false;

        //------------------
        //  ファイル内容表示
        //------------------
        print('<table class="table">');
        $fileArrayLength = count($fileArray);
        for($i = 0; $i<$fileArrayLength; $i++){
            // 後ろから取り出す
            $value = array_pop($fileArray);

            // 空文字であればスキップ
            if($value == ""){
                continue;
            }
            
            // 日付のみ取得
            $pattern = "/\d{4}\/\d{1,2}\/\d{1,2}/";
            preg_match($pattern, $value , $this->date );

            // メッセージのみ取得
            $value = htmlspecialchars_decode($value);
            $pattern = "/<div>.*<\/div>/";
            preg_match($pattern, $value , $this->message);
            $this->message = mb_substr($this->message[0], stripos( $this->message[0], ">")+1, strripos( $this->message[0], "<")-5);

            // 画像名のみ取得
            $pattern = '/src=".*?"/';
            preg_match($pattern, $value , $this->imageName);
            $this->imageName = mb_substr($this->imageName[0], stripos( $this->imageName[0], '"')+1, strripos( $this->imageName[0], '"') - 5);

            // 画像表示位置取得
            if( preg_match(TextFile::WRITE_TAG_STR1_PATTERN, $value) ) {
                $this->imagePos = TextFile::IMAGE_POS_TOP;
            }
            if( preg_match(TextFile::WRITE_TAG_STR2_PATTERN, $value) ) {
                $this->imagePos = TextFile::IMAGE_POS_BOTTOM;
            }
            // 表示
            $write  = '<TR><TD data-toggle="modal" data-target="#formModal" ';
            $write .= sprintf('onclick="doChangeUpdateForm(\'%s\', \'%s\', \'%s\', %s);" >', $this->date[0], $this->message, $this->imageName, $this->imagePos);
            print( $write );
            print($value);
            print('</TD></TR>');
        }
        print('</table>');
        //----------------------------------

        return true;
    }

    //---------------
    //  更新メソッド
    //---------------
    public function update(){

        // 変更対象データ = 変更前データを取得
        $preData = $this->getPreData();

        // 変更データを取得
        $updateData = $this->getUpdateData();
        // ファイルアップロード時にエラーが起きた場合
        if( $updateData === false) return false;

        //----------------------------------------
        //  ファイルの対象行 更新処理
        //  仕様: テキストファイルを読み込んで作り直す。 
        //----------------------------------------

        // ファイル内容を改行コードごとに、Arrayへ設定する
        $fileArray = $this->getFileValueArray();
        if( !$fileArray ) return false;

        $updateKey = null; // 更新対象行のKey
        // 更新対象行を検索
        foreach ($fileArray as $key => $value) {
            // 更新する行と一致すれば
            if($value === $preData){
            $updateKey = $key;
            }
        }

        if($updateKey === null){
            $this->result = "ファイルの更新対象行 が見つかりませんでした";
            return false;
        }
        // 更新対象行を入力したデータに上書きする。
        $fileArray[$updateKey] = $updateData;

        // ファイルオープン と　ロック
        $fp = $this->fileOpenAndLock(TextFile::FILE_WRITE_MODE);
        if(!$fp) return false;

        // ファイル書き込み
        foreach($fileArray as $key => $value){
            if( $value == "") continue;

            $value = $value . "\n";
            fwrite($fp,$value);
        }
        //----------------------------------------

        // ファイルクローズ と アンロック
        if( $this->fileCloseAndUnlock($fp) ) return false;

        $this->result = "ファイルの対象行 更新に成功しました";
        return true;
    }

    //---------------
    //  削除メソッド
    //---------------
    public function delete(){

        // 変更対象データ = 変更前データを取得
        $preData = $this->getPreData();

        //----------------------------------------
        //  ファイルの対象行 削除処理
        //  仕様: テキストファイルを読み込んで作り直す。 
        //----------------------------------------

        // ファイル内容を改行コードごとに、Arrayへ設定する
        $fileArray = $this->getFileValueArray();
        if( !$fileArray ) return false;

        $deleteKey = null; // 削除対象行のKey
        // ファイル内容配列を更新
        foreach ($fileArray as $key => $value) {
            // 更新する行と一致すれば
            if($value === $preData){
            $deleteKey = $key;
            }
        }
        if($deleteKey === null){
            $this->result = "ファイルの削除対象行 が見つかりませんでした";
            return false;
        }

        // 対象行を削除する
        // array_splice($fileArray, $deleteKey, $deleteKey);
        $fileArray[$deleteKey] = "";

        // ファイル削除   ファイルアップロード時のみ
        if($_POST["pre_image_name"]){
            unlink( TextFile::FILE_SAVE_PATH . $_POST["pre_image_name"] );
        }

        // ファイルオープン と　ロック
        $fp = $this->fileOpenAndLock(TextFile::FILE_WRITE_MODE);
        if(!$fp) return false;

        // 書き込み
        foreach($fileArray as $key => $value){
            // 空文字であれば書き込まない
            if( $value == "") continue;
            // 改行コードを行の最後に追加
            $value = $value . "\n";
            // 書き込み
            fwrite($fp,$value);
        }
        // ファイルクローズ と アンロック
        if( $this->fileCloseAndUnlock($fp) ) return false;
        //----------------------------------

        $this->result = "ファイルの対象行 削除に成功しました";
        return true;
    }

//
//
// ↓↓↓↓↓　からはこのクラス内のみで使う共通関数を定義する。
//
// 基本的にエラーが発生した場合は、falseを返すようにしています。


    //-------------------------
    //  ファイルオープン、ロック
    //-------------------------
    private function fileOpenAndLock($mode){

        $modeValue = null;
        switch ($mode) {
            case TextFile::FILE_READ_MODE:
                $modeValue = 'r';
                break;
            
            case TextFile::FILE_WRITE_MODE:
                $modeValue = 'w';
                break;

            case TextFile::FILE_ADD_MODE:
                $modeValue = 'ab';
                break;
        }

        // ファイルオープン
        $fp = fopen( TextFile::FILE_PATH, $modeValue);
        if( !$fp ){
            $this->result = "ファイルオープンに失敗しました";
            return false;
        }

        // ファイルロック
        $lockResult = flock($fp, LOCK_SH);
        if( !$lockResult ){
            $this->result = "ファイルロックに失敗しました";
            return false;
        }

        return $fp;
    }

    //-----------------------------
    //  ファイルクローズ、アンロック
    //-----------------------------
    private function fileCloseAndUnlock($fp){
        // ファイルロックを解放
        $unlockResult = flock($fp, LOCK_UN); 
        if( !$unlockResult ){
            $this->result = "ファイルロック解除に失敗しました";
            return false;
        }

        // ファイルをクローズする
        $closeResult = fclose($fp);
        if( !$closeResult ){
            $this->result = "ファイルクローズに失敗しました";
            return false; 
        }
    }

    //-----------------------------
    //  ファイル内容取得メソッド
    //  返り値 : 改行コードごとの配列
    //-----------------------------
    private function getFileValueArray() {

        // テキストファイルの全内容を文字列で取得
        $fileValue = file_get_contents(TextFile::FILE_PATH);
        // 改行コードを \n にすべて置き換える
        str_replace(array("\r\n","\r"), "\n", $fileValue);
        // 改行コードごとに、文字列を分割
        $fileArray = split("\n",$fileValue);

        return $fileArray;
    }

    //---------------------------------
    //  更新、削除で使う 変更前データ取得
    //---------------------------------
    private function getPreData() {

        $preDate      = $_POST["pre_date"];
        $preMessage   = str_replace(array("\r\n","\r","\n"), "<br>", $_POST["pre_message"]);
        $preImageName = $_POST["pre_image_name"];
        $preImagePos  = $_POST["pre_image_pos"];
        if($preImagePos == TextFile::IMAGE_POS_TOP){
            $preData = sprintf(TextFile::WRITE_TAG_STR1, $preDate, $preImageName, $preMessage);
        }else{
            $preData = sprintf(TextFile::WRITE_TAG_STR2, $preDate, $preMessage, $preImageName);
        }

        return $preData;
    }

    //------------------------------
    //  更新で使う 変更後データ取得
    //------------------------------
    private function getUpdateData(){

        $this->date      = $_POST["date"];
        $this->message   = str_replace(array("\r\n","\r","\n"), "<br>", $_POST["message"]);

        // ファイルアップロード
        if( !$this->fileUpload() ) return false;

        $this->imagePos = $_POST["image_pos"];
        if($this->imagePos == TextFile::IMAGE_POS_TOP){
            $updateData = sprintf(TextFile::WRITE_TAG_STR1, $this->date, $this->imageName, $this->message);
        }else{
            $updateData = sprintf(TextFile::WRITE_TAG_STR2, $this->date, $this->message, $this->imageName);
        }

        return $updateData;      

    }

    //-----------------------------
    // ファイルアップロードメソッド
    //-----------------------------
    private function fileUpload(){

        // 必須ではない場合、未選択であればファイルアップロードしない。
        if($_FILES["image_file"]["error"] == UPLOAD_ERR_NO_FILE){
            return true;
        }

        // エラーチェック
        if( $this->errorCheck() ) return false;

        // ファイル名設定
        date_default_timezone_set('Asia/Tokyo');
        $uploadDate = date('YmdHis', time() );
        $extension = pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION);  // 拡張子を取得。 exctension = 拡張子
        $fileName = $uploadDate . "." . $extension;
        $this->imageName = $fileName;

        // ファイルアップロード
        $moveResult = move_uploaded_file($_FILES["image_file"]["tmp_name"], TextFile::FILE_SAVE_PATH.$fileName);
        if(!$moveResult){
            $this->result = "ファイルアップロードに失敗しました";
            return false;
        }
        return true;
    }

    //-------------------------------------
    // ファイルアップロード時のエラーチェック
    //-------------------------------------
    private function errorCheck(){

        // $_FILES['file']['error'] アップロード後のエラーコードが設定されている。
        switch( $_FILES["image_file"]["error"] ){
            case UPLOAD_ERR_OK: // ファイルアップロード成功
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->result .= "ファイルの最大サイズを超えています。";
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->result .= "アップロードされたファイルは一部のみしかアップロードされていません。";
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->result .= "ファイルが選択されていない可能性があります。";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->result .= "テンポラリフォルダがありません。";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $this->result .= "ディスクへの書き込みに失敗しました。";
                break;
            default: //上記以外のエラーコードが返された場合
                $this->result .= "ファイルアップロードに失敗しました。";
                break;
        }

        // 拡張子チェック
        if($this->result == ""){ //エラーがなければチェックするという条件
            // アップロードファイル名を取得
            $uploadFileName = $_FILES["image_file"]["name"];
            // 拡張子を取得。 exctension = 拡張子
            $extension = pathinfo($uploadFileName, PATHINFO_EXTENSION);
            // 拡張子チェック
            $errorChkFlg = false;
            foreach ($this->errorChkExtension as $value) {
                if( $extension == $value ){
                    $errorChkFlg = true;
                    break;
                }
            }
            if(!$errorChkFlg) $this->result .= "画像以外のファイルをアップロードしています。";
        }

        // エラーがあればtrue, なければ false
        $errorFlg = ($this->result != "") ? true : false;
        return $errorFlg;
    }
}
?>