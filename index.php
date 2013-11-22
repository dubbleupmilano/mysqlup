<?php
//require_once ($_SERVER['DOCUMENT_ROOT'].'/open.php');
define ('LOGDB_ERROR_FILE', 'db_error.log');
define ('LOGDB_QUERY_FILE', 'db_query.log');

error_reporting(E_ALL ^ E_NOTICE);
require_once('Common.php');
require_once('Db.php');


$get_db1 = Common::getVariable('db1');
$get_db2 = Common::getVariable('db2');
$do = Common::getVariable('do');

if ( $do == 'checkdb' )
{
	$db1 = $get_db1;
	$db2 = $get_db2;
}
else
{
	$db1['host'] = DB_HOST;
	$db1['user'] = DB_USER;
	$db1['password'] = DB_PASSWORD;
	$db1['name'] = '';

	$db2['host'] = DB_HOST;
	$db2['user'] = DB_USER;
	$db2['password'] = DB_PASSWORD;
	$db2['name'] = '';
}
?>
  <script>
	function toggleDetail( idDiv ) {

		var objDiv = document.getElementById( idDiv );

		if(objDiv.style.display=='')
		{
			objDiv.style.display='none';
		}
		else
		{
			objDiv.style.display='';
		}
	}
  </script>
  <style>
	BODY, TD, DIV { font-family:Arial; font-size:12px; }
	A IMG { border:none; }
	.db_connection {

	}

	.db_schema {
		empty-cells:show;
	}

	.db_schema TR TH {
		font-size:14px;
		font-weight:bold;
		text-align:left;
		background-color:#6F93C9;
		color:#FFF;
		padding:2px;
	}
	.db_schema TR:nth-child(odd) {
		background-color:#FAFAFA;
	}

	.db_schema TR:nth-child(even) {
		background-color:#FFF;
	}

	.table_status {
		font-family:Arial;
		empty-cells:show;
		width:40%;	
	}
	.table_status TR TH {
		font-size:11px;
		font-weight:bold;
		text-align:left;
		background-color:#333;
		color:#FFF;
		padding:2px;
	}
	.table_status TR TD {
		font-size:12px;
		border:1px solid #999;
	}	
	.table_status TR TD:first-child {
		background-color:#CCC;
		font-weight:bold;
		width:200px;
	}
	.table_status TR TD.mod_attr {
		background-color:#FF0;
		color:#CC0000;
		font-weight:bold;
	}
	
	
	.table_schema {
		font-family:Arial;
		empty-cells:show;
		width:80%;
	}
	.table_schema TR TH {
		font-size:11px;
		font-weight:bold;
		text-align:left;
		background-color:#6F93C9;
		color:#FFF;
		padding:2px;
	}
	.table_schema TR TD {
		font-size:12px;
		border:1px solid #999;
	}

	.table_schema TR:nth-child(odd) {
		background-color:#FAFAFA;
	}

	.table_schema TR:nth-child(even) {
		background-color:#FFF;
	}

	.table_schema TR TD:first-child {
		background-color:#C6D5EA;
		font-weight:bold;
		width:200px;
	}

	.table_schema TR TD.new_field {
		background-color:#FF0;
		font-weight:bold;
		color:#00AA00;
	}
	.table_schema TR TD.mod_attr {
		background-color:#FF0;
		color:#CC0000;
		font-weight:bold;
	}
	.table_header {
		font-family:Arial;
		font-size:14px;
		font-weight:bold;
		color:#6F93C9;
		margin:3px;
		margin-top:20px;
	}
	
	.sql_block {
		background-color:#FFF;
		border:2px solid #6F93C9;
		padding:6px 6px;
		position:absolute;
		width:200px;
	}
	.sql_string {
		font-family:Arial;
		font-size:11px;
		font-weight:bold;
		color:#000;
	}

	.missing_table {
		font-size:14px;
		font-weight:bold;
		color:#CC0000;
	}

  </style>

  <form action="?" method="POST">
	<input type="hidden" name="do" value="checkdb">
  <table class="db_connection">
	<tr>
		<td><img src="db_connection_34x34.png" align="absmiddle"> <strong>DATABASE 1</strong></td>
		<td><img src="db_connection_34x34.png" align="absmiddle"> <strong>DATABASE 2</strong></td>
	</tr>
	<tr>
		<td>
			<table>
				<tr>
					<td>HOST</td>
					<td><input type="text" name="db1[host]" placeholder="<? print $db1['host']; ?>"></td>
				</tr>
				<tr>
					<td>USERNAME</td>
					<td><input type="text" name="db1[user]" placeholder="<? print $db1['user']; ?>"></td>
				</tr>
				<tr>
					<td>PASSWORD</td>
					<td><input type="password" name="db1[password]" placeholder="<? print $db1['password']; ?>"></td>
				</tr>
				<tr>
					<td>DATABASE</td>
					<td><input type="text" name="db1[name]" placeholder="<? print $db1['name']; ?>"></td>
				</tr>
			</table>
		</td>
		<td>
			<table>
				<tr>
					<td>HOST</td>
					<td><input type="text" name="db2[host]" placeholder="<? print $db2['host']; ?>"></td>
				</tr>
				<tr>
					<td>USERNAME</td>
					<td><input type="text" name="db2[user]" placeholder="<? print $db2['user']; ?>"></td>
				</tr>
				<tr>
					<td>PASSWORD</td>
					<td><input type="password" name="db2[password]" placeholder="<? print $db2['password']; ?>"></td>
				</tr>
				<tr>
					<td>DATABASE</td>
					<td><input type="text" name="db2[name]" placeholder="<? print $db2['name']; ?>"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan=2 align="center"><input type="submit" value="Go!"></td>
	</tr>

  </table>
  </form>
<?
if ( !$do == 'checkdb' ) {
	die();
}

$ObjDb1 = new Db($db1['host'], $db1['user'], $db1['password'], $db1['name'] );
$sql = "SHOW TABLES";
$rs_tables = $ObjDb1->query( $sql );
$db1_table_count = $ObjDb1->getNumRows($rs_tables);
$db1_struct = getDbStruct($ObjDb1, $rs_tables);

mysql_data_seek($rs_tables,0);
while ( $t = mysql_fetch_row($rs_tables ) ) {
	$db1_table_list[] = $t[0];
}

$ObjDb2 = new Db($db2['host'], $db2['user'], $db2['password'], $db2['name'] );
$sql = "SHOW TABLES";
$rs_tables = $ObjDb2->query( $sql );
$db2_table_count = $ObjDb1->getNumRows($rs_tables);
$db2_struct = getDbStruct($ObjDb2, $rs_tables);

mysql_data_seek($rs_tables,0);
while ( $t = mysql_fetch_row($rs_tables ) ) {
	$db2_table_list[] = $t[0];
}
$table_list = array_unique(array_merge($db1_table_list, $db2_table_list));
sort($table_list);

$c = 0;
foreach ( $table_list AS $table_name ) {
	$merge[$c]['dbleft'] = null;
	$merge[$c]['dbright'] = null;
	if ( array_key_exists($table_name, $db1_struct) && array_key_exists($table_name, $db2_struct) )
	{
		compareFields( $db1_struct[$table_name], $db2_struct[$table_name] );
		compareFields( $db2_struct[$table_name], $db1_struct[$table_name] );
	}

	if ( array_key_exists($table_name, $db1_struct) )
	{
		$merge[$c]['dbleft'] = $db1_struct[$table_name];
	}

	if ( array_key_exists($table_name, $db2_struct) )
	{
		$merge[$c]['dbright'] = $db2_struct[$table_name];
	}

	$c++;
}

?>

<table width="100%" class="db_schema">
	<tr>
		<th><img src="db.Schema.24x24.png" align="absmiddle"> <? print $db1['name']; ?>: <br/><? print $db1_table_count; ?> Tables</th>
		<th><img src="db.Schema.24x24.png" align="absmiddle"> <? print $db2['name']; ?>: <br /><? print $db2_table_count; ?> Tables</th>
	</tr>

	<?
		$uid = 900;
		foreach( $merge as $item )
		{
			$uid++;
	?>
	<tr>
		<td width="50%" valign="top" >
			<?
				if ( is_array($item['dbleft'] ) )
				{
					$html = makeTableBlock( $item['dbleft']);
					print $html;
				}
				else
				{
					print "<img src='message_warning.png' align='absmiddle'>";
					print '<em>MISSING TABLE: <span class="missing_table">' . $item['dbright']['table_name'] .'</span></em>  ';
					$sql = "SHOW CREATE TABLE " . $item['dbright']['table_name'];
					$create_tb = $ObjDb2->getRecord($sql , "Create Table");

					$sql_block = makeSqlInfoBlock( 'sql_block_' . $uid++, $create_tb );
					print $sql_block;
				}
			?>
		</td>
		<td valign="top">
			<?
				if ( is_array($item['dbright'] ) )
				{
					$html = makeTableBlock( $item['dbright']);
					print $html;
				}
				else
				{
					print "<img src='message_warning.png' align='absmiddle'>";
					print '<em>MISSING TABLE: <span class="missing_table">' . $item['dbleft']['table_name'] .'</span></em>  ';
					$sql = "SHOW CREATE TABLE " . $item['dbleft']['table_name'];
					$create_tb = $ObjDb1 ->getRecord($sql , "Create Table");

					$sql_block = makeSqlInfoBlock( 'sql_block_' . $uid++, $create_tb );
					print $sql_block;
				}
			?>
		</td>


	</tr>
	<?

		}
	?>
</table>

<?

function getDbStruct( $ObjDb, $rs_tables )
{
	$db_struct = Array();

	while ( $table = mysql_fetch_row($rs_tables) )
	{
		$sql = "SHOW COLUMNS FROM " . $table[0];
		$rs_fields = $ObjDb->query( $sql );

		$tb_fields = Array();
		while ( $field_attr = mysql_fetch_assoc($rs_fields) )
		{
			$tb_fields[ $field_attr["Field"] ] = $field_attr;
		}

		$sql = "SELECT COUNT(*) AS tot_rows FROM " . $table[0];
		$num_rows = $ObjDb->getrecord( $sql , 'tot_rows' );
		
		$sql = "SHOW TABLE STATUS FROM `" . $ObjDb->database . "` where Name = '" . $table[0] . "'";	
		$rs_status = $ObjDb->query( $sql );
		$tb_status = Array();
		$tb_status = mysql_fetch_assoc($rs_status);
		
		$db_struct[ $table[0] ] = Array( 'table_name' => $table[0], 'num_rows' => $num_rows, 'fields' => $tb_fields, 'status' =>  $tb_status );
	
	}

	return $db_struct;
}

function compareFields( &$table_left, $table_right )
{
	$prev_field = '';
	foreach ( $table_left['fields'] AS $field_name => $attr ) {
		if ( array_key_exists( $field_name, $table_right['fields'] ) ) {
			foreach ( $attr AS $n => $v ) {
				if ( $n == 'Field' )
					continue;

				if ( $table_right['fields'][$field_name][$n] != $v ) {
					$sql = "ALTER TABLE `".$table_left['table_name']."` CHANGE `$field_name` `$field_name` $v";
					//$sql = "ALTER TABLE `".$table_left['table_name']."` CHANGE `$field_name` `$field_name` " . $table_right['fields'][$field_name][$n];
					$table_left['conflict'][ $field_name ][$n] = $sql;
				}
			}
		}
		else {
			$attributes = '';
			$attributes .= ' ' . $attr['Type'];
			$sql = "ALTER TABLE `".$table_left['table_name']."` ADD `$field_name` $attributes AFTER `$prev_field`";

			$table_left['fields'][ $field_name ]['is_new'] = $sql;
		}

		$prev_field = $field_name;
	}
	
	foreach ( $table_left['status'] AS $field_name => $attr ) {
		if ( array_key_exists( $field_name, $table_right['status'] ) && $table_right['status'][$field_name] != $attr) {		
			$table_left['status_conflict'][ $field_name ] = true;
		}
	}	

}

function makeSqlInfoBlock ( $id, $sql )
{
	$s = '';
	$s = "<a href='javascript:void(0)' onClick='toggleDetail(\"$id\")'><img src='sql.png'></a>";
	$s .= "<div id='$id' class='sql_block' style='display:none;'>";
	$s .= "<div style='text-align:right;'><a href='javascript:void(0)' onClick='toggleDetail(\"$id\")'><img src='tiny_cancel.png'></a></div>";
	$s .= "<div class='sql_string'>$sql</div>";
	$s .= "</div>";
	return $s;
}


function makeTableBlock( $table )
{
	global $uid;
	$status_check_fields = Array('Engine', 'Collation' );
	$html = Array();

	$table_name = $table['table_name'];
	$num_rows = $table['num_rows'];

	$html[] = "<div class='table_header'><img src='db.Table.24x24.png' align='absmiddle'> $table_name <span style='font-weight:normal;'>($num_rows rows)</span></div>";
	if ( count($table['status_conflict']) > 0  ) {
		
		$show_conflict = false;
		$conflict_rows = Array();
		foreach ( $table['status_conflict'] AS $status => $val ) {
			if ( in_array( $status, $status_check_fields ) ) {
				$show_conflict = true; //display conflicts only for fields in $status_check_fields
				$conflict_rows[] =  "<tr><td >$status</td><td class='mod_attr'>".$table['status'][$status]."</td></tr>";
			}
		}
		if ( $show_conflict  ) {
			$html[] = "
					<div id='div_status' style='padding-left:30px;'>
					<table class='table_status'>
						<tr>
							<th>Property</th>
							<th>Value</th>
						</tr>
					";	
			$html[] = implode("\n",$conflict_rows);					
			$html[] = '</table></div>';
		}
	}
	
	$fields = $table['fields'];
	$conflict = $table['conflict'];

	$html[] = "
		<div id='table_$table_name' style='padding-left:30px;'>
		<table class='table_schema'>
			<tr>
				<th>Name</th>
				<th>Type</th>
				<th>Null</th>
				<th>Key</th>
				<th>Default</th>
				<th>Extra</th>
			</tr>
		";
	foreach ( $fields AS $field_name => $attr_list )
	{
		$html[] = "<tr>";
		foreach ( $attr_list AS $attr_name => $attr_value )
		{
			$class = '';
			if ( $attr_list['is_new'] )
			{
				$class = 'new_field';
			}

			if ( $attr_name == 'is_new' )
			{
				$attr = '';
				$sql_block = makeSqlInfoBlock( 'sql_block_' . $uid++, $attr_value );
				$attr .= $sql_block;
			} else {
				if ( $conflict[$field_name][$attr_name] )
				{
					$class = 'mod_attr';

					$sql_block = makeSqlInfoBlock( 'sql_block_' . $uid++, $conflict[$field_name][$attr_name] );
					$attr_value .= ' ' . $sql_block;
				}
				$attr = $attr_value;

			}
			$html[] =  "<td class='$class'>" . $attr . '</td>';
		}

		$html[] = '</tr>';
	}
	$html[] = '</table>
			</div>';
	$html_string = implode("\n",$html);
	return $html_string;
}
?>
