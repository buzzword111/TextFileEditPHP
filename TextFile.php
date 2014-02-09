<?php

class TextFile{
	// 入力された日付
	protected $date;
	// 入力されたメッセージ
	protected $message;

	// 入力されたメッセージ
	protected $result;

	// タグのエスケープ判断フラグ
	protected $tagEscapeFlg = false;
	// 編集するファイルパス
	const FILE_PATH = "./sample.txt";


	public function regist(){

		// ファイルオープン
        $fp = fopen( TextFile::FILE_PATH, 'ab');
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

        //--------------
        //  ファイル登録
        //--------------
        $this->date    = $_POST["date"];
        // メッセージ内の改行を<br>タグに置き換える
        $this->message = str_replace(array("\r\n","\r","\n"), "<br>", $_POST["message"]);

        $data = sprintf('<span class="date">%s</span><br>%s',$this->date, $this->message);
        $data = $data . "\n";
        fwrite($fp,  $data);

        flock($fp, LOCK_UN);
        fclose($fp);
        //----------------------------------

        $this->result = "ファイルの登録に成功しました";
        return true;
	}


	public function viewFileValue(){

		// ファイルオープン
        $fp = fopen( TextFile::FILE_PATH, 'r');
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


        //------------------
        //  ファイル内容表示
        //------------------

		// ファイル内容を改行コードごとに、Arrayへ設定する

		$fileValue = file_get_contents(TextFile::FILE_PATH);
		str_replace(array("\r\n","\r"), "\n", $fileValue);
		$fileArray = split("\n",$fileValue);

        // ファイル内容を後ろから表示

        print('<table class="table">');
		$fileArrayLength = count($fileArray);
        for($i = 0; $i<$fileArrayLength; $i++){
            $value = array_pop($fileArray);

            if($value == ""){
            	continue;
            }
            
            // 日付のみ取得
            $pattern= "/\d{4}\/\d{1,2}\/\d{1,2}/";
            preg_match($pattern, $value , $this->date );
            // $this->date = mb_ereg_replace('[^0-9]', '.', $this->date[0]);

            // メッセージのみ取得
            $value = htmlspecialchars_decode($value);

            $this->message = mb_substr($value, stripos( $value, "<br>")+4);

            $write  = '<TR><TD data-toggle="modal" data-target="#formModal" ';
            $write .= sprintf('onclick="doChangeUpdateForm(\'%s\', \'%s\');" >', $this->date[0], $this->message);
            print( $write );
            print($value);
            print('</TD></TR>');
        }
        print('</table>');
        
        flock($fp, LOCK_UN); // ファイルロックを解放
        fclose($fp);         // ファイルをクローズする
        //----------------------------------

        return true;
	}

	public function update(){

		// ファイルオープン
		$fp = fopen( TextFile::FILE_PATH, 'r');
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


        //------------------
        //  ファイル内容表示
        //------------------

		// ファイル内容を改行コードごとに、Arrayへ設定する
		$fileArray = array();
        while( !feof($fp) ){
            $value = fgets($fp);
            if($value == ""){
            	continue;
            }

            $value = str_replace(array("\r\n","\r","\n"), "", $value);
	    	// Arrayに追加する
            array_push($fileArray, $value);
        }

        flock($fp, LOCK_UN); // ファイルロックを解放
        fclose($fp);         // ファイルをクローズする

		$fp = fopen( TextFile::FILE_PATH, 'w');

		// $lockResult = flock($fp, LOCK_SH);

        $this->date    = $_POST["date"];
        // メッセージ内の改行を<br>タグに置き換える
        $this->message = str_replace(array("\r\n","\r","\n"), "<br>", $_POST["message"]);
        $data = sprintf('<span class="date">%s</span><br>%s',$this->date, $this->message);

        $preDate    = $_POST["pre_date"];
        $preMessage = str_replace(array("\r\n","\r","\n"), "<br>", $_POST["pre_message"]);
        $preData = sprintf('<span class="date">%s</span><br>%s',$preDate, $preMessage);


        $updateKey = null;
        // ファイル内容配列を更新
        foreach ($fileArray as $key => $value) {
    		var_dump($value);
        	// 更新する行と一致すれば
        	if($value === $preData){
        		var_dump("一致しましたよ！");
        		$updateKey = $key;
        	}
        }
        // 書き込み
        foreach($fileArray as $key => $value){
        	if($key === $updateKey){
        		$value = $data;
        	}
            fwrite($fp,$value);
        }

        flock($fp, LOCK_UN); // ファイルロックを解放
        fclose($fp);         // ファイルをクローズする

        //----------------------------------

        return true;
	}

	public function delete(){

		// ファイルオープン
		$fp = fopen( TextFile::FILE_PATH, 'r');
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


        //------------------
        //  ファイル内容表示
        //------------------

		// ファイル内容を改行コードごとに、Arrayへ設定する
		$fileArray = array();
        while( !feof($fp) ){
            $value = fgets($fp);
            if($value == ""){
            	continue;
            }

            $value = str_replace(array("\r\n","\r","\n"), "", $value);
	    	// Arrayに追加する
            array_push($fileArray, $value);
        }

        flock($fp, LOCK_UN); // ファイルロックを解放
        fclose($fp);         // ファイルをクローズする

		$fp = fopen( TextFile::FILE_PATH, 'w');

		// $lockResult = flock($fp, LOCK_SH);

        $this->date    = $_POST["date"];
        // メッセージ内の改行を<br>タグに置き換える
        $this->message = str_replace(array("\r\n","\r","\n"), "<br>", $_POST["message"]);
        $data = sprintf('<span class="date">%s</span><br>%s',$this->date, $this->message);

        $preDate    = $_POST["pre_date"];
        $preMessage = str_replace(array("\r\n","\r","\n"), "<br>", $_POST["pre_message"]);
        $preData = sprintf('<span class="date">%s</span><br>%s',$preDate, $preMessage);


        $deleteKey = null;
        // ファイル内容配列を更新
        foreach ($fileArray as $key => $value) {
        	// 更新する行と一致すれば
        	if($value === $preData){
        		$deleteKey = $key;
        	}
        }

        var_dump($deleteKey);
        var_dump($fileArray);
        array_splice($fileArray, $deleteKey, $deleteKey);
		var_dump($fileArray);
        // 書き込み
        foreach($fileArray as $key => $value){
            fwrite($fp,$value);
        }

        flock($fp, LOCK_UN); // ファイルロックを解放
        fclose($fp);         // ファイルをクローズする

        //----------------------------------

        return true;
	}
}
?>