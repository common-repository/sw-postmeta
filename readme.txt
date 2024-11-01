=== SW PostMeta ===
Contributors: Scuderia-Web
Donate link: http://scuderia-web.com/
Tags: title, post, meta, seo, admin
Requires at least: 2.5.1
Tested up to: 2.5.1
Stable tag: 1.21

記事・ページ毎にMETA keywordsとdescriptionを登録します。
This is a plug-in that registers keywords and description each entry. 


== Description ==
**It is being developed by the Japanese. The explanation in English doesn't exist or there is a possibility that the grammar is not accurate.**

このプラグインは[ScuderiaWeb](http://scuderia-web.com/)によって開発された記事・ページ毎にMETAタグのkeywordsとdescriptionを登録するプラグインです。

This is a plug-in that registers keywords and description of the META tag of the article and each page. 

投稿ページ（管理ページ）から記事と同時に登録することができます。
テンプレートから呼び出した際、未設定の場合（空での登録は可能。過去の記事などの場合）は、デフォルト値を表示します。

*  記事・ページ毎にMETAタグのkeywordsとdescriptionを登録
* 投稿ページ（管理ページ）から記事と同時に登録
* 管理画面からまとめて登録・管理可能
* 未設定時用のデフォルト値を登録可能


== Installation ==
1. ダウンロード後解凍して出来たsw_post_meta.phpを開き、先頭（16行目～）の初期設定を書き換えます。
2. WordPressのpluginsフォルダにアップロードします。
3. 管理画面の「プラグイン」からSW PostMetaを有効化します。
4. 記事及びページの投稿画面・管理画面から、keywordsとdescriptionを設定します。
5. 複数の記事をまとめて登録する場合や、確認する場合は、管理画面の「管理」→「SW PostMeta」より行います。
6. テーマファイルで関数を呼び出して使用します。


== Screenshots ==
[ScuderiaWebのプラグインページ](http://scuderia-web.com/wordpress-plugin/sw_post_meta.php)をご覧ください。

== Function ==

キーワードの取得

**sw_getMetaKey(post_id, print)**

説明文の取得

**sw_getMetaDesc(post_id, print)**

引数（共通）

* post_id - 記事・ページのID。省略時はデフォルト値が返ります
* print - 画面に出力の有無。0:しない, 1:する（初期値）



== Frequently Asked Questions ==
現在準備中です。

== How To Ask Questions ==
不具合、ご要望、質問等は下記までご連絡ください。
[wp@scuderia-web.com](mailto:wp@scuderia-web.com)

== Changelog ==
* 1.21 - 2008.11.22 - ReadMe.txtの修正
* 1.12 - 2008.08.22 - ホームの場合、デフォルト値を表示するように変更
* 1.11 - 2008.06.06 - 一覧を階層表示するように変更
* 1.1 - 2008.06.03 - 一覧機能追加
* 1.0 - 2008.03.16 - リリース
