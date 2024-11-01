<?php
/*
Plugin Name: SW PostMeta
Plugin URI: http://scuderia-web.com/wordpress-plugin/sw_post_meta.php
Description: 記事・ページ毎にMETAタグのkeywordsとdescriptionを登録可能にします。
Version: 1.21
Author: Scuderia-Web
Author URI: http://scuderia-web.com
*/

/*
リリースノート
1.21  	2008.11.22  	ReadMe.txtの修正
1.12  	2008.08.22  	ホームの場合、デフォルト値を表示するように変更
1.11 	2008.06.06 		一覧を階層表示するように変更
1.1 	2008.06.03 		一覧機能追加
1.0 	2008.03.16 		リリース
*/

/* 初期設定 */
// デフォルトのkeywords
$DEFAULT_KEY = 'キーワード1,キーワード2,キーワード3,キーワード4,キーワード5';
// デフォルトのdescription
$DEFAULT_DESC = 'Webサイトの説明文Webサイトの説明文Webサイトの説明文Webサイトの説明文Webサイトの説明文Webサイトの説明文';

/* ========================================================================== */

/* インストール */
$install = (basename($_SERVER['SCRIPT_NAME']) == 'plugins.php' && isset($_GET['activate']));
if ($install) {
	$cls = new sw_postmeta();
	$cls->install();
}

/* Hook */
add_action('edit_form_advanced',	array('sw_postmeta', 'dispMetaField'));
add_action('edit_page_form',		array('sw_postmeta', 'dispMetaField'));
add_action('save_post',				array('sw_postmeta', 'registMeta'));
add_action('delete_post',			array('sw_postmeta', 'deleteMeta'));
add_action('edit_post',				array('sw_postmeta', 'registMeta'));
// 管理画面
add_filter('admin_head', array('sw_postmeta', 'insertCSS'));
add_action('admin_menu', array('sw_postmeta', 'addMenu'));

/* function */
function sw_getMetaKey($post_id = 0, $print = 1) {
	if(is_home) {
		$key = $GLOBALS['DEFAULT_KEY'];
	} else if(sw_postmeta::getMetaCnt($post_id) > 0) {
		$meta_data = sw_postmeta::getMeta($post_id);
		$key = $meta_data->key;
	} else {
		$key = $GLOBALS['DEFAULT_KEY'];
	}
	
	if($print == 1) {
		echo $key;
		return;
	} else {
		return $key;
	}
}
function sw_getMetaDesc($post_id = 0, $print = 1) {
	if(is_home) {
		$desc = $GLOBALS['DEFAULT_DESC'];
	} else if(sw_postmeta::getMetaCnt($post_id) > 0) {
		$meta_data = sw_postmeta::getMeta($post_id);
		$desc = $meta_data->desc;
	} else {
		$desc = $GLOBALS['DEFAULT_DESC'];
	}
	
	if($print == 1) {
		echo $desc;
		return;
	} else {
		return $desc;
	}
}

/* ----- [ CLASS ] ----- */
class sw_postmeta {
	var $indent_num = 0;

	/* ///////////////////////////////// 表示 ///////////////////////////////// */
	/* 投稿フィールド表示 */
	function dispMetaField($post_id) {
		$meta_key = $GLOBALS['DEFAULT_KEY'];
		$meta_desc = $GLOBALS['DEFAULT_DESC'];
		if($_REQUEST['post']) {
			if(sw_postmeta::getMetaCnt($_REQUEST['post']) > 0) {
				$get_meta = sw_postmeta::getMeta($_REQUEST['post']);
				$meta_key = $get_meta->key;
				$meta_desc = $get_meta->desc;
			}
		}
?>
		<fieldset id="sw_post_meta" class="postbox">
			<h3 class="dbx-handle">META</h3>
			<div id="field_sw_post_meta" class="dbx-content inside">
				<table>
					<tr>
						<th style="text-align: right">Keywords</th>
						<td><input type="text" style="width:500px" name="F_sw_post_meta_key" value="<?= $meta_key ?>" /></td>
					</tr>
					<tr>
						<th style="text-align: right">Description</th>
						<td><input type="text" style="width:500px" name="F_sw_post_meta_desc" value="<?= $meta_desc ?>" /></td>
					</tr>
				</table>
			</div>
		</fieldset>
<?php
	}
	
	// 管理画面
	function addMenu() {
		if (!current_user_can('manage_options'))
			return;

		$minUserLevel = 1;

		if (function_exists('add_options_page')) {
			add_management_page('SW PostMeta', 'SW PostMeta', $minUserLevel,
			basename(__FILE__), array('sw_postmeta', 'dispManagePage'));
		}
	}
	function insertCSS() {
		if(strcmp($_SERVER['SCRIPT_NAME'],"/wp-admin/edit.php") != 0 || strcmp($_GET['page'], "sw_post_meta.php") != 0)
			return;
		
		echo '
	<style type = "text/css">
	<!--
		tr.key_over .key_len, tr.key_over .input_key { color: #f00; }
		tr.desc_over .desc_len, tr.desc_over .input_desc { color: #f00; }
	-->
	</style>
			';
	}
	function dispManagePage() {
		
		// 登録処理
		if (isset($_POST['submit']))
			sw_postmeta::manage();
		
		// 表示
?>
<div class=wrap>
	<form method="post">
		<h2>SW PostMeta</h2>
		<ul>
			<li>削除する場合はブランクにする。</li>
			<li>Keywordsは1000バイト以内、Descriptionは160バイト以内。超過したものは赤で表示。</li>
		</ul>

		<div class="submit">
			<input type="submit" name="submit" value="登録" />
		</div>
		
		<p>表示条件：
<?php
		switch($_GET['list']) {
			case 1:		// 登録済みのみ表示
				$results = sw_postmeta::getMetaAll();
				echo "登録済みのみ表示";
				break;
			case 2:		// 公開済みを全て表示
				$results = sw_postmeta::getAllPublishPost();
				echo "公開済みを全て表示";
				break;
			case 3:		// 全て表示
				$results = sw_postmeta::getAllPost();
				echo "全て表示";
				break;
			default:
				$results = sw_postmeta::getMetaAll();
				echo "登録済みのみ表示";
				break;
		}
		
		switch($_GET['target']) {
			case "post":
				echo "（投稿のみ）";
				break;
			case "page":
				echo "（ページのみ）";
				break;
			default:
				break;
		}

?>
		　　表示件数：<?= count($results)."件"; ?>
			</p>

		<div class="tablenav">
			<a href="?page=sw_post_meta.php&list=1">登録済みのみ表示</a>
				（<a href="?page=sw_post_meta.php&list=1&target=post">投稿のみ</a>・
				<a href="?page=sw_post_meta.php&list=1&target=page">ページのみ</a>）&nbsp;
			<a href="?page=sw_post_meta.php&list=2">公開済みを全て表示</a>
				（<a href="?page=sw_post_meta.php&list=2&target=post">投稿のみ</a>・
				<a href="?page=sw_post_meta.php&list=2&target=page">ページのみ</a>）&nbsp;
			<a href="?page=sw_post_meta.php&list=3">全て表示</a>
				（<a href="?page=sw_post_meta.php&list=3&target=post">投稿のみ</a>・
				<a href="?page=sw_post_meta.php&list=3&target=page">ページのみ</a>）&nbsp;
		</div>
		<table class="widefat">
			<thead>
				<tr><th>ID</th><th>Title</th><th>Keywords<br />Description</th></tr>
			</thead>
			<tbody>
<?php	sw_postmeta::dispRecord($results) ?>
			</tbody>
		</table>
		<div class="submit">
			<input type="submit" name="submit" value="登録" />
		</div>
	</form>
</div>
<?php
	}
	
	function dispRecord($results) {
		global $indent_num;
		for($i = 1; $i <= $indent_num; $i++) {
			$indent .= "―&nbsp;&nbsp;";
		}

		foreach($results as $result) {
?>
				<tr	class="meta<?php if(strlen($result->key) > 1000) { echo ' key_over'; } ?><?php if(strlen($result->desc) > 160) { echo ' desc_over'; } ?>">
					<td><?= $result->ID ?></td>
					<td><?= $indent.$result->post_title ?></td>
					<td nowrap>
						<input type="text" style="width:600px" name="<?= $result->ID ?>[key]" value="<?= $result->key ?>" class="input_key" />
						<span class="key_len">(<?= strlen($result->key) ?>)</span>
						<br />
						<input type="text" style="width:600px" name="<?= $result->ID ?>[desc]" value="<?= $result->desc ?>" class="input_desc" />
						<span class="desc_len">(<?= strlen($result->desc) ?>)</span>
					</td>
				</tr>
<?php
			$children = sw_postmeta::getMetaAll($result->ID);
			if(count($children) > 0) {
				$indent_num++;
				sw_postmeta::dispRecord($children);
				$indent_num--;
			}
		}
	}
	
	/* ///////////////////////////////// 処理 ///////////////////////////////// */
	function manage() {
		foreach($_POST as $key=>$value) {
			if(strcmp($key,"submit") == 0)
				continue;
			sw_postmeta::deleteMetaData($key);	// 削除
			if(strcmp($value['key'], "") == 0 && strcmp($value['desc'], "") == 0) {
				// 削除
			} else {
				sw_postmeta::insertMetaData($key, $value['key'], $value['desc']);
			}
		}
	}
	
	function registMeta($post_id) {
		if($post_id)
			$cnt = sw_postmeta::getMetaCnt($post_id);
		else
			$cnt = 0;
		
		if($cnt > 0)
			sw_postmeta::updateMetaData($post_id, $_POST['F_sw_post_meta_key'], $_POST['F_sw_post_meta_desc']);
		else
			sw_postmeta::insertMetaData($post_id, $_POST['F_sw_post_meta_key'], $_POST['F_sw_post_meta_desc']);
	}
	function deleteMeta($post_id) {
		
		if($post_id)
			$cnt = sw_postmeta::getMetaCnt($post_id);
		else
			$cnt = 0;
		
		if($cnt > 0)
			sw_postmeta::deleteMetaData($post_id);
	}
	
	/* ///////////////////////////////// エラーチェック ///////////////////////////////// */


	/* ///////////////////////////////// DB ///////////////////////////////// */
	/* テーブル生成 */
	function install() {
	    $wpdb =& $GLOBALS['wpdb'];
	    $sql = "SHOW TABLES LIKE '%".sw_postmeta::post_meta_table()."%'";
	    $results = $wpdb->query($sql);
  
		if ($results == 0) {
			$sql = "create table ".sw_postmeta::post_meta_table()."
					(
					`id` integer not null,
					`key` text,
					`desc` text,
					primary key(id)
					)";

			$wpdb->query($sql);
		}
	}
	
	/* 新規 */
	function insertMetaData($id, $key = '', $desc = '') {
	    $wpdb =& $GLOBALS['wpdb'];
	    $sql = "insert into ".sw_postmeta::post_meta_table()."(`id`, `key`, `desc`) values ('$id', '$key', '$desc')";
	    $results = $wpdb->query($sql);
	}
	
	/* 更新 */
	function updateMetaData($id, $key = '', $desc = '') {
	    $wpdb =& $GLOBALS['wpdb'];
	    $sql = "update ".sw_postmeta::post_meta_table()." set `key` = '$key', `desc` = '$desc' Where id = $id";
	    $results = $wpdb->query($sql);
	}
	
	/* 削除 */
	function deleteMetaData($id) {
	    $wpdb =& $GLOBALS['wpdb'];
	    $sql = "delete from ".sw_postmeta::post_meta_table()." Where id = $id";
	    $results = $wpdb->query($sql);
	}
	
	/* 登録済みのみ */
	function getMetaAll($parent = 0) {
	    $wpdb =& $GLOBALS['wpdb'];
	    $sql = "Select PM.key, PM.desc, P.ID, P.post_title, P.post_parent From ".sw_postmeta::post_meta_table()." PM left join ".
	    		$wpdb->posts." P on PM.`id` = P.`ID` Where 1 = 1";
	    if(isset($_GET['target']))
	    		$sql .= " And P.post_type = '".$_GET['target']."'";
	    $sql .= " And P.post_parent = $parent Order By P.post_type DESC, P.menu_order";

    	return $wpdb->get_results($sql);
    	
	}
	
	/* 公開済みを全て */
	function getAllPublishPost($parent = 0) {
	    $wpdb =& $GLOBALS['wpdb'];
	    $sql = "Select PM.key, PM.desc, P.ID, P.post_title, P.post_parent From ".sw_postmeta::post_meta_table()." PM right join ".
	    		$wpdb->posts." P on PM.`id` = P.`ID` Where post_status = 'publish'";
	    if(isset($_GET['target']))
	    		$sql .= " and P.post_type = '".$_GET['target']."'";
	    $sql .= " And P.post_parent = $parent Order By P.post_type DESC, P.menu_order";
	    
    	return $wpdb->get_results($sql);
	}
	
	/* 全て */
	function getAllPost($parent = 0) {
	    $wpdb =& $GLOBALS['wpdb'];
	    $sql = "Select PM.key, PM.desc, P.ID, P.post_title, P.post_parent From ".sw_postmeta::post_meta_table()." PM right join ".
	    		$wpdb->posts." P on PM.`id` = P.`ID` Where 1 = 1";
	    if(isset($_GET['target']))
	    		$sql .= " And P.post_type = '".$_GET['target']."'";
	    $sql .= " And P.post_parent = $parent Order By P.post_type DESC, P.menu_order";
	    
    	return $wpdb->get_results($sql);
	}
	
	/* 取得 */
	function getMeta($id) {
	    $wpdb =& $GLOBALS['wpdb'];
	    $sql = "Select * From ".sw_postmeta::post_meta_table()." Where id = $id";
    	return $wpdb->get_row($sql);
	}
	
	/* 存在確認用件数取得 */
	function getMetaCnt($id) {
	    $wpdb =& $GLOBALS['wpdb'];
	    $sql = "Select Count(*) as cnt From ".sw_postmeta::post_meta_table()." Where id = $id";
    	return $wpdb->get_var($sql);
	}

	/* テーブル名 */
	function post_meta_table() {
		return $GLOBALS['table_prefix'].'sw_postmeta';
	}

}
?>