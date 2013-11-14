<?

class Common
{
	function __construct()
	{

	}
	
	static public function getVariable($var)
	{
		if ( isset($_POST[$var]) )
		{
			if ( get_magic_quotes_gpc() )
			{
				$output = Common::arrayStripSlashes( $_POST[$var] );
			}
			else
			{
				$output = $_POST[$var];
			}
			return  $output;
		}
		else if ( isset($_GET[$var]) )
		{
			if ( get_magic_quotes_gpc() )
			{
				$output =  Common::arrayStripSlashes( $_GET[$var] );
			}
			else
			{
				$output = $_GET[$var];
			}
			return  $output;
		}
		else 
		{
			return false;
		}
	}

	static public function getIntVar($var) {

		$output = Common::getVariable($var);
		return intval( $output );
	}

	static public function getBoolVar($var) {

		$output = Common::getVariable($var);
		return (bool) $output;
	}

	static function goToPage($url){
		/*
    	print(SharedData::getTplVar("action_path")) ;
		print("GET => ".var_export($_GET , true)."\n") ;
		print("POST => ".var_export($_POST  , true)."\n") ;
    	print("SESSION => ".var_export($_SESSION  , true)."\n") ;


    	$_SESSION['_console']['query'] = SharedData::getTplVar("query_log") ;
    	$_SESSION['_console']['std_out'] = ob_get_contents() ;
*/
    	ob_clean() ;

 
		//header("Location: $url") ;
		print "<script>window.location.href = '".$url."'</script>";
    	die() ;
    }
    
    static public function getNewOrderNum() {

        $code_fill_len = 4;

        $sql = "SELECT MAX(id) AS last_id_order FROM purchase_order";
        $last_id_order = Db::getInstance()->getRecord($sql,"last_id_order");

        if(intval($last_id_order)==0) {
            $last_id_order = 1;
        }

        if(strlen($last_id_order)>=$code_fill_len) {
            $code_fill_len = strlen($last_id_order)+1;
        }
          $prefix = "";

          $letters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
          $lettersLength = strlen($letters)-1;

          for($i = 0 ; $i < 3 ; $i++)
          {
              $prefix .= $letters[rand(0,$lettersLength)];
          }

        return $prefix . str_pad($last_id_order, $code_fill_len , '0', STR_PAD_LEFT) . "-TR";
    }
    
    static function escapeJavascript($string){
		return strtr($string, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
    }

	static public function makeArrayForCombo( $array, $key_field, $value_field, $use_key = false ,$first_empty = false )
	{
		$arrCombo = array();
		
		if($first_empty){
			$arrCombo[''] = "" ;
		}
		
		foreach ( $array AS $key => $item )
		{
			if ( $use_key )
			{
				$arrCombo[ $key ] = $item[ $value_field ];
			}
			else
			{
				$arrCombo[ $item[$key_field] ] = $item[ $value_field] ;
			}
		}
	
		return $arrCombo;
	}
	
	
	static public function isMySqlDate($date)
	{
		return $this->isDate( $date, 'ymd', '-' );
	}

	
	static public function isDate( $sDate, $sFormat = 'ymd', $sSep = '-' )
	{
	  $f['y'] = 4;
	  $f['m'] = 2;
	  $f['d'] = 2;
	
	  if( ereg( "([0-9]{".$f[$sFormat[0]]."})".$sSep."([0-9]{".$f[$sFormat[1]]."})".$sSep."([0-9]{".$f[$sFormat[2]]."})", $sDate ) ){
	    
		$y        = strpos( $sFormat, 'y' );
	    $m        = strpos( $sFormat, 'm' );
	    $d        = strpos( $sFormat, 'd' );
	    $aDates  = explode( $sSep, $sDate );
	
	    return @checkdate( $aDates[$m], $aDates[$d], $aDates[$y] );
	  }
	  else
	    return false;
	}

	static function dateItalianExtensive($_date) {

		$mesi = array(1=>'gennaio', 'febbraio', 'marzo', 'aprile',
				 'maggio', 'giugno', 'luglio', 'agosto',
				 'settembre', 'ottobre', 'novembre','dicembre');

		$giorni = array('domenica','lunedì','martedì','mercoledì',
				 'giovedì','venerdì','sabato');

		$timestamp = strtotime($_date);
		$it_date = ucfirst($giorni[date('w',$timestamp)]) . ' ' . date('d', $timestamp ) . ' '.  ucfirst($mesi[date('n',$timestamp)]) . ' ' . date('Y', $timestamp );
        
		return $it_date;
	}

	static function dataEurToMysql($dataEur)
	{
		$rsl = explode ('/',$dataEur);
		$rsl = array_reverse($rsl);
		return implode($rsl,'-');
	}

	/*
	 * Funzione per ordinare un array multidimensionale
	 * Il primo parametro è l'array da ordinare
	 * Il secondo è il nome del campo dell'array che si vuole ordinare, può essere un int o string
	 * */
	static public function arraySortMulti($array, $key)
	{
		if(count($array)<1)
		{
		return $array;
		} 
		
		for ($i = 0; $i < sizeof($array); $i++) 
		{
			if(! empty($array[$i][$key]))
			{
				$sort_values[$i] = $array[$i][$key];
			}else{
				$sort_values[$i] = $array[$i];
			}
		}
		asort ($sort_values);
		reset ($sort_values);
		while (list ($arr_keys, $arr_values) = each ($sort_values)) 
		{
			$sorted_arr[] = $array[$arr_keys];
		}
		return $sorted_arr;
	} 	
	
	
	static public function arrayStripSlashes( $var )
	{
		if ( is_array($var) )
		{	
			foreach ( $var AS $key => $item )
			{
				if ( is_array($item) )
				{
					$var[$key] = Common::arrayStripSlashes( $item );
				}
				else
				{
					$var[$key] = stripslashes($item);
				}
			}
		}
		else
		{
			$var = stripslashes($var);
		}
		return $var;
	}

	static public function arrayToString( &$a )
	{
		$s = '{';
		foreach ( $a AS $k => $v )
		{
			if ( is_array($v) )
			{
				$s .= "[$k]=" . Common::arrayToString( $v );	
			}
			else {
				$s .= "[$k]=$v;";
			}
		}	
		$s .= "}";
		return $s;
	}
	
	static public function arrayAddSlashes( $var )
	{
		if ( is_array($var) )
		{	
			foreach ( $var AS $key => $item )
			{
				if ( is_array($item) )
				{
					$var[$key] = Common::arrayAddSlashes( $item );
				}
				else
				{
					$var[$key] = addslashes($item);
				}
			}
		}
		else
		{
			$var = addslashes($var);
		}
		return $var;
	}
	
	/*
	  Initialising new array to the first element of the given array.
	  Check whether current element in initial array has already been added to new array.
	  If yes break to save us some time. If no, then add current element to new array.
	*/

	static public function bi_array_unique($array, $row_element) {

	    $new_array[0] = array_shift( $array );
	    
	    if(empty($array))
	    	return $new_array; 
	    	
	    foreach ($array as $current) 
	    {
	        $add_flag = 1;
	        foreach ($new_array as $tmp) 
	        {
	            if ($current[$row_element]==$tmp[$row_element]) 
	            {
	                $add_flag = 0; 
					break;
	            }
	        }
	        if ($add_flag) 
	        	$new_array[] = $current;
	    }
		return $new_array;
	} // end function remove_dups

    /** 
     * Restituisce un array con gli id estrapolati dalla lista <BR/>
     * Questo array viene estrapolato dalla parentList 
     *
     * @access       public
     * @return            <code>array</code> id estrapolati; 
     *
     * @since           1.0
     * @example     La parentList da trasformare deve avere questa sintassi <33><15><86><3>
     * 
     */
	static public function taggedListToArray( $tag_list ) {
    
		if( trim($tag_list) == '')
    	{
    		return Array();
		}
        $split = explode('><',substr($tag_list,1,-1));
        return $split;
    }

	static public function mergeTaggedList( $tag_list_1, $tag_list_2 )
	{
		$split_1 = Common::taggedListToArray( $tag_list_1 );
		$split_2 = Common::taggedListToArray( $tag_list_2 );
		$merged = array_unique( array_merge( $split_2, $split_1) );

		if ( is_array($merged) && count($merged) > 0 )
		{
			$tag_merged = '<' . join('><', $merged) . '>';
		}
		else
		{
			$tag_merged = '';
		}

		return $tag_merged;
	}

	static public function itemInTaggedList ( $item, $tagged_list )
	{
		$check_array = Common::taggedListToArray( $tagged_list );
  
		if( in_array( $item, $check_array ) )
		{
			return true;
		}

		return false;
	}

	static public function customSubLinks(Array $sub_links, $func_code , $visible, Array $params = array() )
    {
    	 if(!is_array($sub_links))
    	 {
    	 	return false;	 
		 }
    	 if(trim($func_code)=="")
    	 {
    	 	 return $sub_links;
		 }
		 
		 foreach($sub_links as $key=>$func)
		 {
			 if($func['code'] == $func_code )
			 {                
		 		if($visible === false)
		 		{                                                       
		 			unset($sub_links[$key]);
				}
				elseif($params)
				{
					foreach($params as $name=>$value)
					{
					 	$sub_links[$key]['exec'] .= "&".$name."=".$value;
					}		
				}
			 }
		 }
		 
		 return $sub_links;     
    }

	static public function makeTreeFromArray( $array, $id_parent )
	{
		foreach ( $array AS $item )
		{
			if ( $item['id_parent'] == $id_parent )
			{
				$child[$item['id']]['node'] = $item;
				$child[$item['id']]['children'] = Common::makeTreeFromArray( $array,  $item['id']);
			}
		}
		return $child;
	}

	static public function makeDeepArrayFromTree ( $tree, &$out, $level = 0)
	{
		if ( is_array($tree) )
		{
			foreach ( $tree AS $item )
			{
				$node = $item['node'];

				$index =  count($out);
				$out[ $index ] = $node;
				$out[ $index ]['level'] = $level;
				if ( count($item['children']) > 0 )
				{
					Common::makeDeepArrayFromTree ( $item['children'], $out , $level+1 );
				}
			}
		}
	}
	   
    static public function makeNewPassword( ) {

    	$new_password = Common::makeNewPasswordFromDb();

    	return $new_password;
	}


	private function makeNewPasswordFromDb( $letters = 6, $numbers = 3 )
	{
		global $System;

		$pwd = '';
		$word_length = $letters;

		$sql = "SELECT id,word FROM words WHERE LENGTH(word) = $word_length ORDER BY RAND() LIMIT 0,1";
		$row = $System->Db->getRecord($sql);

		$upper_limit = pow(10,$numbers)-1;
		$digits = rand(0,$upper_limit);
		$digits = str_pad($digits, $numbers, "0", STR_PAD_LEFT);
		$pwd = $row['word'] . $digits;

		return $pwd;
	}

	private function makeNewPasswordRandom( $length )
	{
		$pwd = '';

		for ($i = 1; $i <= $length ; $i++)
		{
			$char = '';
			while ( $char == '' )
			{
				// generate random character between "0" and "z"
				$rnd = rand(48,122);
				// consider only numbers 0-9 (ascii 48-57) or lowercase letters (ascii 97-122)
				if ( ( 48 <= $rnd && $rnd <= 57 ) || ( 97 <= $rnd && $rnd <= 122 ) )
				{
					$char = chr($rnd);
					//print "<br>char = $char,";
				}
			}
			$pwd .= $char;
		}

		return $pwd;
	}

    static public function setNavBarValues($start, $tot_rec, $rec_x_page)
	{
		$navbar = array();

		$navbar["tot_rec"] = $tot_rec;
		$navbar["tot_pages"] = ceil($tot_rec / $rec_x_page);
		$navbar["current_page"] = ceil($start / $rec_x_page)+1;
		if($start > 0){
			$navbar["prev_start"] = $start - $rec_x_page;
			if ($navbar["prev_start"] < 0){
				$navbar["prev_start"] = 0;
			}
		} else {
			$navbar["prev_start"] = -1;
		}

		if ($start + $rec_x_page < $tot_rec - 1){
			$navbar["next_start"] = $start + $rec_x_page;
			if ($navbar["next_start"] > $tot_rec){
				$navbar["next_start"] = $tot_rec;
			}
		}

		return $navbar;
	}
	
	static public function imageIsResizable( $image_type )
	{
		if ($image_type ==  IMAGETYPE_GIF	||
			$image_type ==  IMAGETYPE_JPEG	||
			$image_type ==  IMAGETYPE_PNG) {
				return true;
		} else {
			return false;
		}
		
	}

	static public function resizeImg( $source_img, $dest_img, $max_width, $max_height, $crop) {

		if (!file_exists($source_img)) {
			return false;
		}

		$image_size = getimagesize($source_img);
		$image_type = $image_size[2];

		switch($image_type) {
			case IMAGETYPE_GIF:
				$srcImage = @imagecreatefromgif($source_img);
				break;

			case IMAGETYPE_JPEG:
				$srcImage = @imagecreatefromjpeg($source_img);
				break;

			case IMAGETYPE_PNG:
				$srcImage = @imagecreatefrompng($source_img);
				break;

			default:
				return false;
				break;
		}

		if(!$srcImage)
		{
			$img_info = pathinfo($source_img);
			return false;
		}

		$source_h = imagesy($srcImage);
		$source_w = imagesx($srcImage);

		$delta_w = ( $source_w - $max_width );
		$delta_h = ( $source_h - $max_height );

		$ratio = $source_w / $source_h;

		if ( $delta_w > $delta_h ) {

			$new_h = ( $source_h > $max_height ) ? $max_height : $source_h;
			$new_w = $new_h * $ratio;

		} else {

			$new_w = ( $source_w > $max_width ) ? $max_width : $source_w;
			$new_h = $new_w / $ratio;
		}

		$new_w = intval($new_w);
		$new_h = intval($new_h);

		if ( $crop == true ) {

			$srcImageResized = imagecreatetruecolor( $new_w, $new_h );

			imagecopyresampled($srcImageResized, $srcImage, 0,0,0,0, $new_w, $new_h, $source_w, $source_h);

			$offset_w = ($new_w - $max_width) / 2;
			$offset_h = ($new_h - $max_height) / 2;

			$destImage = imagecreatetruecolor( $max_width, $max_height );

			imagecopyresampled($destImage, $srcImageResized, 0, 0, $offset_w, $offset_h, $max_width, $max_height, $max_width, $max_height);
		}
		else
		{
			if($max_width==$max_height && ($new_w>$max_width || $new_h>$max_height))
			{
				$ratio = $new_w/$new_h;
				if($new_w>$new_h)
				{
					$new_w = intval($max_width);
					$new_h = intval($new_w/$ratio);
				}
				else
				{
					$new_h = intval($max_height);
					$new_w = intval($new_h*$ratio);
				}
			}

			$destImage = imagecreatetruecolor($new_w,$new_h);
			imagecopyresampled($destImage, $srcImage, 0,0,0,0, $new_w, $new_h, $source_w, $source_h);
		}

		$path_parts = pathinfo($dest_img);

		if (!file_exists($path_parts['dirname'])) {
			@mkdir($path_parts['dirname']);
		}

		ImageJpeg($destImage, $dest_img, 100);

		return array("width"	=> $new_w,	"height"	=> $new_h);

	}

	public static function replaceTrueFalse( $value=false , $extra_true='' , $extra_false='') {

		$img_true = 'true.gif';
		$img_false = 'false.gif';

		if($extra_true!='')
			$img_true = $extra_true;

		if($extra_false!='')
			$img_false = $extra_false;

		if($value==1 || $value===true || strtolower($value)=='ok' || strtolower($value)=='si')
		{
			return "<img src=\"".A2F_IMG_URL."/$img_true\" title=\"".$value."\" />";
		}
		else
		{
			return "<img src=\"".A2F_IMG_URL."/$img_false\" title=\"".$value."\" />";
		}
	}

	static public function return_bytes($val) {
	    $val = trim($val);
	    $last = strtolower($val{strlen($val)-1});
	    switch($last) {
	        // The 'G' modifier is available since PHP 5.1.0
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }

	    return $val;
	}
	
	public static function getMicrotimeFloat( $get_as_float = false )
	{
	    list($msec, $sec) = explode(" ", microtime($get_as_float));
	    return ((float)$sec + (float)$msec );
	}
	
	public static function getMicrotimeString()
	{
	    list($msec, $sec) = explode(" ", microtime());

	    //return ( date('Y-m-d H:i:s',(float)$sec) . ' ' . sprintf('%d',(float)$msec * 1000) . ' msec');
	    return ( date('Y-m-d H:i:s',(float)$sec) . ' ' . round( (float)$msec * 1000 ) . ' msec');
	}

	public static function truncate($string, $max = 20, $rep = '')
	{
		if (strlen($string) <= ($max + strlen($rep)))
		{
			return $string;
		}
		$leave = $max - strlen ($rep);
		return substr_replace($string, $rep, $leave);
	}
	
	public static function getReferer (){
		return $_SERVER['HTTP_REFERER'] ;
	}

	public static function highlightPhrase(Array $needles, $haystack, $words=5, $css_class="") {

		$array_founded_values=array();

		//$haystack_split = explode ( " " , $haystack);
		$haystack_split = preg_split("/[\s,.:-;]+/" , $haystack);

		foreach( $needles as $index_arr=>$needle ) {

			//echo "CERCO: _".$needle."_<br />";
			//print_r($haystack_split);
			$needle = trim($needle);
			$highlight_text="";
			$keys = array_keys ( $haystack_split , $needle , false );

			foreach($keys as $key) {

				$start_key = $key-$words;
				$end_key = $words*2 + 2;

				$start_dots = "...";

				if($start_key<0) {
					$start_key=0;
					$start_dots="";
				}

				$consist_values = array_slice($haystack_split, $start_key, $end_key);

				if($css_class=="") {
					$consist_values[$words] = "<span style='background-color:yellow;'>" . $consist_values[$words] . "</span>";
				}
				else {
					$consist_values[$words] = "<span class='$css_class'>" . $consist_values[$words] . "</span>";
				}

				$highlight_text .= $start_dots.join(" ", $consist_values)."...<br />";

			}

			if(count($keys)>0) {
				$array_founded_values[$index_arr] = $highlight_text;
			}
		}

		return $array_founded_values;
	}

	public static function getRegionFromProvince( $id_provincia ) {

		$id_provincia = intval($id_provincia);
		if($id_provincia==0) {
			return array();
		}

		$sql = "SELECT regioni.*
				FROM regioni
				INNER JOIN provincie ON regioni.id = provincie.id_regione
				WHERE provincie.id = " . $id_provincia;

		return Object::getDb()->getRecord($sql);
	}

	public static function getRegioni( $id_regione=0 ) {

		$sql = "SELECT * FROM regioni WHERE 1";
		if(intval($id_regione)>0)
		{
			$sql .= " AND id = ".intval($id_regione)." ";
		}
		$sql .= " ORDER BY name ";

		return Object::getDb()->getRecordSet($sql);
	}

	public static function getProvincia( $id_regione=0 , $id_prov=0 ) {

		$sql = "SELECT * FROM provincie WHERE 1 ";
		if(intval($id_regione)>0)
		{
			$sql .= " AND id_regione = ".intval($id_regione)." ";
		}
		if(intval($id_prov)>0)
		{
			$sql .= " AND id = ".intval($id_prov)." ";
		}
		$sql .= " ORDER BY name ";

		return Object::getDb()->getRecordSet($sql);
	}


	public static function getProvinceFromName( $name ) {

		$sql = "SELECT * FROM provincie WHERE name = '".addslashes(trim($name))."' ";

		return Object::getDb()->getRecord($sql);
	}

	public static function getIdProvince( Array $filter ) {
		$table = "provincie" ;
		
		$criteria = "1 " ;
		$arrayTable = Array($table) ;
		$startPage = "1" ;
		$rec4page = "" ;
		
		$left_join = "" ;
		
		/**
		 * Filter section
		 */
		foreach($filter as $k => $v){
			switch($k){
				case "code" :
				case "name" :
					//String Like Search
					$criteria .= " AND `".$table."`.`$k` LIKE '".addslashes($v)."' ";
				break ;
				case "id" :
				case "id_province" :
					if($v != "" && $v != 0){
						if(!is_array($v)){
							$v = Array($v) ;
						}
						
						if(is_array($v)){
							$vList = implode(",",$v) ;
							
							$criteria .= " AND `".$table."`.`$k` IN (".$vList.") ";
						}
						else {
							$criteria .= " AND `".$table."`.`$k` is null ";
						}
					}
				break ;
				case "deleted" :
					
				break ;
				case "limit_result" :
					$rec4page = intval($v) ;
				break ;
				case "start_page" :
					$startPage = intval($v) ;
				break ;
				case "order_by" :
					$orderFields = Array(
						"id" => "`".$table."`.`id`" ,
						"province" => "`".$table."`.`business_name`" ,
						"city" => "`".$table."`.`city`" ,
						"province" => "`provincie`.`name`" ,
					) ;
					
					if(isset($orderFields[$filter[$k]])){
						$filter[$k] = $orderFields[$filter[$k]] ;
					}
					else  {
						unset($filter[$k]) ;
					}
					
					if($v == "province"){
						$left_join .= " LEFT JOIN provincie ON provincie.id = id_province " ;
					}
				break ;
			}	
		}
		
		$limit = "" ;
		$order = "" ;
		
		if(isset($rec4page) && $rec4page != ""){
			$limit = $rec4page ;
			
			if($startPage > 1){
				$limit = (($startPage-1)*$rec4page).",".$limit ;
			}
		}
		
		$order = ((isset($filter['order_by']))?$filter['order_by']:'') ;
		$sort = ((isset($filter['sort']))?$filter['sort']:'');
		$order = (($order == '')?"name ASC":$order.(($sort == "DESC")?"DESC":"ASC")) ;
		
		$tableList = implode(" , ",$arrayTable) ;
		
		/**
		 * Define the query base,
		 * 	this query will be split in 2
		 * 		1) extract the id of the table limited  ($query)
		 * 		2) Extract the count of id without limit ($queryTotal)
		 */
		$queryBase = "SELECT  {columns} ".
				 		"FROM $tableList ".
				 		"$left_join ".
				 		"WHERE $criteria ";
		
		$column = "`".$table."`.`id` " ;
		$query = str_replace("{columns}" , $column , $queryBase).
				 "GROUP BY `".$table."`.`id` ".
				 (($order != "")?"ORDER BY $order ":"") .
				 (($limit != "")?"LIMIT $limit ":"") ;

		if($ris = Db::getInstance()->query($query)){
			$array = Array() ;

			while($row = mysql_fetch_assoc($ris)){
				$array[] = $row ;
			}
			
			return $array ;
		}
		else {
			//????Come gestiamo gli errori?
		}
		
		return false;
	}
	
	public static function getCity( $id_prov=0 , $id_regione=0 , $id_comune=0 ) {

		$sql = "SELECT * FROM comuni WHERE 1 ";
		if(intval($id_regione)>0)
		{
			$sql .= " AND id_regione = ".intval($id_regione)." ";
		}
		if(intval($id_prov)>0)
		{
			$sql .= " AND id_provincia = ".intval($id_prov)." ";
		}
		if(intval($id_comune)>0)
		{
			$sql .= " AND id = ".intval($id_comune)." ";
		}
		$sql .= " ORDER BY nome ";
 
		return Object::getDb()->getRecordSet($sql);
	}

	public static function getCountry($id_state = null){
		$sql_where = "" ;
		
		if(!is_null($id_state)){
			if(!is_array($id_state)){
				$id_state = Array(intval($id_state)) ;
			}

			$sql_where = "WHERE `id` IN (".join("," , $id_state).")";
		}
		
		$query = "SELECT * FROM `country` ".$sql_where ;
		return Db::getInstance()->getRecordSet($query);
	}
	
	public static function setLastDo($do){
		$_SESSION['last_do'] = $do ;
	}
	
	public static function getLastDo(){
		return $_SESSION['last_do'] ;
	}
	
	/**
	 * Return an array whit only value containing needle string
	 *
	 * @param string $needle
	 * @param array $haystack
	 * 
	 * @return mixed
	 */
	public static function searchInArray($needle ,Array $haystack){
		$return_array = Array() ;
		
		foreach($haystack as $k => $v){
			if(strpos($v , $needle) !== false)
				$return_array[$k] = $v ;
		}

		return $return_array;
	}

	public static function distance($lat1, $lon1, $lat2, $lon2, $unit = "K") {

		$theta = $lon1 - $lon2; 
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
		$dist = acos($dist); 
		$dist = rad2deg($dist); 
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);
		
		if ($unit == "K") {
			return number_format( ($miles * 1.609344) , 2);
		} else if ($unit == "N") {
			return number_format( ($miles * 0.8684) , 2);
		} else {
			return number_format( $miles , 2);
		}
	}

    public static function parseArrayWithInifile( &$data = Array() )
	{
		if ( is_array( $data ) )
		{
			foreach ( $data AS &$item ) {
				if ( is_array($item) ) {
					Common::parseArrayWithInifile( $item );
				}
				else {
					Common::parseStringWithInifile( $item );
				}
			}
		} else {
			Common::parseStringWithInifile( $data );
		}
	}

    	/**
	 * Parse string and replace #XYZ# with item in in file
	 *
	 */
	public static function parseStringWithInifile( &$str )
	{
		$pattern = '/\#(.*)\#/U';
		$str = preg_replace_callback(
					$pattern,
					"Common::replaceStr",
					$str);
	}

    	/**
	 * Replace #XYZ# with item in in file
	 *
	 */
	static function replaceStr( $matches )
	{
		$ini_content = SharedData::getData('lang_dictionary');
		$current_lang = SharedData::getData('lang');

		if ( $ini_content[ $current_lang ] [ $matches[1] ] ) {
			$s = $ini_content[ $current_lang ] [ $matches[1] ];
		}

		return $s;
	}

    static function systemExceptionMail(Exception $e,$subject) {

        $Mail = new MailCustom();

        $Mail->setFrom("system@toprounders.com","TopRounders SysAgent");

        $Mail->AddAddress("devwave@gmail.com" , "Andrea Prenz");
       // $Mail->AddAddress("andrea.mezzanotte@gmail.com" , "Andrea Mezzanotte");

        $Mail->setSubject($subject);

        $body = "Data: " . date(DATETIME_FORMAT_PHP)."<br /><br />";
        $body .= "Codice: " . $e->getCode()."<br /><br />";
        $body .= "File: " . $e->getFile()."<br /><br />";
        $body .= "Line: " . $e->getLine()."<br /><br />";
        $body .= "Message: " . $e->getMessage()."<br /><br />";
        $body .= "Trace: " . $e->getTraceAsString()."<br /><br />";
        $body .= "Eccezione: " . $e->__toString();

        $Mail->setBody($body);

        $Mail->Send();
    }

    static function systemErrorMail($error,$subject) {

        $Mail = new MailCustom();

        $Mail->setFrom("system@toprounders.com","TopRounders SysAgent");

        $Mail->AddAddress("devwave@gmail.com" , "Andrea Prenz");
       // $Mail->AddAddress("andrea.mezzanotte@gmail.com" , "Andrea Mezzanotte");

        $Mail->setSubject($subject);

        $body = "Attenzione si verificato un errore critico sul sito TopRounders.com<br /><br />ERRORE:<br />";

        $Mail->setBody($body.$error);

        $Mail->Send();
    }

	public static function calcDistanceStoreInspector(Store $Store = null , Inspector $Inspector = null){

		$query = "REPLACE INTO distance_inspector_store ".
				 "SELECT NULL , users.id AS id_inspector, store.id AS id_store, km_from_deg( ".
					"store.latitude, store.longitude, inspector.latitude, inspector.longitude ".
				 ") AS distance ".
				 "FROM store, users, inspector ".
				 "WHERE users.bitmask & ".BITMASK_AREA_INSPECTOR." = ".BITMASK_AREA_INSPECTOR." ".
				 "AND inspector.id_user = users.id ".
				 "AND users.deleted = 0 " ;
		if($Store instanceof Store) {
			$query .= "AND store.id = ".intval($Store->getId()) ;
		}
		
		if($Inspector instanceof Inspector ){
			$query .= "AND inspector.id_user = ".$Inspector->getId() ;
		}
		
		Db::getInstance()->execute($query) ;
		return true ;
	}

	public static function calcDistanceStoreShopper(Store $Store = null , Shopper $Shopper = null){

		$query = "REPLACE INTO distance_shopper_store ".
				 "SELECT NULL , users.id AS id_shopper, store.id AS id_store, km_from_deg( ".
					"store.latitude, store.longitude, shopper.latitude_address, shopper.longitude_address ".
				 ") AS distance ".
				 "FROM store, users, shopper ".
				 "WHERE users.bitmask = ".BITMASK_AREA_SHOPPER." ".
				 "AND shopper.id_user = users.id ".
				 "AND users.deleted = 0 " ;
		if($Store instanceof Store){
			$query .= " AND store.id = ".intval($Store->getId()) ;
		}

		if($Shopper instanceof Shopper ){
			$query .= " AND shopper.id_user = ".intval($Shopper->getId()) ;
		}

		Db::getInstance()->execute($query) ;
		return true ;
	}

	/**
	* Paypal
	*/
     /**
	 * Send HTTP POST Request
	 *
	 * @param	string	The request URL
	 * @param	string	The POST Message fields in &name=value pair format
	 * @param	bool		determines whether to return a parsed array (true) or a raw array (false)
	 * @return	array		Contains a bool status, error_msg, error_no,
	 *				and the HTTP Response body(parsed=httpParsedResponseAr  or non-parsed=httpResponse) if successful
	 *
	 * @access	public
	 * @static
	 */
	function PPHttpPost($url_, $postFields_, $parsed_)
	{
		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url_);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);

		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postFields_);

		//getting response from server
		$httpResponse = curl_exec($ch);

		if(!$httpResponse) {
			return array("status" => false, "error_msg" => curl_error($ch), "error_no" => curl_errno($ch));
		}

		if(!$parsed_) {
			return array("status" => true, "httpResponse" => $httpResponse);
		}

		$httpResponseAr = explode("\n", $httpResponse);

		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}

		if(0 == sizeof($httpParsedResponseAr)) {
			$error = "Invalid HTTP Response for POST request($postFields_) to $url_.";
			return array("status" => false, "error_msg" => $error, "error_no" => 0);
		}
		return array("status" => true, "httpParsedResponseAr" => $httpParsedResponseAr);

	} // PPHttpPost

	/**
	 * Redirect to Error Page
	 *
	 * @param	string	Error message
	 * @param	int		Error number
	 *
	 * @access	public
	 * @static
	 */
	function PPError($error_msg, $error_no) {
		// create a new curl resource
		$ch = curl_init();

		// set URL and other appropriate options
		$php_self = substr(htmlspecialchars($_SERVER["PHP_SELF"]), 1); // remove the leading /
		$redirectURL = SITE_URL."/error.php";
		curl_setopt($ch, CURLOPT_URL, $redirectURL);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		// set POST fields
		$postFields = "error_msg=".urlencode($error_msg)."&error_no=".urlencode($error_no);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postFields);

		// grab URL, and print
		curl_exec($ch);
		curl_close($ch);
	}
	
    static function makeSqlOrderBy( $table_name, Array $orderby = Array() ) {

        $sql_orderby = Array();
        foreach ( $orderby AS $field => $direction )
        {
            $sql_orderby[] = " `$table_name`.`$field` $direction ";
        }

        return implode(',',$sql_orderby);
    } 
}


?>