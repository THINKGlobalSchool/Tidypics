<?php
/**
 * Tidypics CSS
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */
?>

/* ***************************************
	TWEAKS/FIXES
*************************************** */

.elgg-system-messages {
	z-index: 9010; /* Make sure system messages are visible */
}

/* Fix tinyMCE toolbar */
.tidypics-lightbox-comments-container .mceToolbar * {
    white-space: normal !important;
}
.tidypics-lightbox-comments-container .mceToolbar tr,
.tidypics-lightbox-comments-container .mceToolbar td {
    float:left !important;
}

/* ***************************************
	TIDYPICS
*************************************** */
.elgg-module-tidypics-album,
.elgg-module-tidypics-image,
.elgg-module-tidypics-upload {
	width: 161px;
	min-height: 161px;
	text-align: center;
	margin: 5px 0;
}
.elgg-module-tidypics-image,
.elgg-module-tidypics-upload {
	margin: 5px auto;
}

.tidypics-gallery-widget > li {
	width: 100%;
}
.tidypics-photo-wrapper {
	position: relative;
}

.tidypics-heading {
	display: block;
	overflow: hidden;
	height: 16px;
}

.tidypics-heading:hover {
	color: inherit !important;
	text-decoration: none;
}

.tidypics-input-thin {
	width: 120px;
}

#tidypics-sort li {
	width:153px;
	height:153px;
}

.tidypics-river-list > li {
	display: inline-block;
}

.tidypics-photo-item {
	margin-right: 7px;
}

.tidypics-gallery > li {
	padding: 0 10px;
}

.tidypics-album-nav {
	margin: 3px 0;
	text-align: center;
	color: #aaa;
}

.tidypics-album-nav > li {
	padding: 0 3px;
}

.tidypics-album-nav > li {
	vertical-align: top;
}

/* ***************************************
	Tagging
*************************************** */
.tidypics-tagging-border1 {
	border: solid 2px white;
}

.tidypics-tagging-border1, .tidypics-tagging-border2,
.tidypics-tagging-border3, .tidypics-tagging-border4 {
    filter: alpha(opacity=50);
	opacity: 0.5;
}

.tidypics-tagging-handle {
    background-color: #fff;
    border: solid 1px #000;
    filter: alpha(opacity=50);
    opacity: 0.5;
}

.tidypics-tagging-outer {
    background-color: #000;
    filter: alpha(opacity=50);
    opacity: 0.5;
}

.tidypics-tagging-help {
	position: absolute;
	left: 50%;
	top: -25px;
	width: 300px;
	margin-left: -150px;
	text-align: center;
}

.tidypics-tagging-select {
	position: absolute;
	width: 300px;
}

.tidypics-tag-wrapper {
	display: none;
	position: absolute;
}

.tidypics-tag {
	border: 2px solid white;
	clear: both;
}

.tidypics-tag-label {
	float: right;
	margin-top: 5px;
	color: #666;
}

/* ***************************************
	Tagging
*************************************** */
#tidypics_uploader {
	position:relative;
	width:400px;
	min-height:20px;
}

#tidypics_choose_button {
	position:absolute;
	top:0;
	left:0;
	z-index:0;
	display:block;
	float:left;
}

#tidypics_flash_uploader {
	position:relative;
	z-index:100;
}

/* ***************************************
	Custom listings
*************************************** */

div.tidypics-albums-list-container,
div.tidypics-photos-list-container {
	overflow: auto;
	width: 100%;
}

div.tidypics-albums-list-container > div.elgg-module-tidypics-album,
div.tidypics-photos-list-container > div.elgg-module-tidypics-image, 
div.tidypics-photos-list-container > div.elgg-module-tidypics-upload {
	float: left;
	padding: 0 10px;
}

div.tidypics-none {
	width: auto;
}

div.tidypics-none > div{
	border: 5px solid #EEEEEE;
    color: #999999;
    font-weight: bold;
    height: 152px;
    line-height: 151px;
    width: 510px;
}

a.tidypics-load-more {
	display: block;
	margin-left: 9px;
	width: 692px;
}

/* ***************************************
	Upload
*************************************** */

.elgg-module-tidypics-upload > div {
	background: none repeat scroll 0 0 #EFEFEF;
	border: 1px dashed #CCCCCC;
	overflow: hidden;
	color: #777777;
	font-weight: bold;
	line-height: 159px;
	text-align: center;
	cursor: pointer;
}

div#tidypics-upload-container {
	min-width: 800px;
	min-height: 500px;
	overflow: hidden;
}

div.tidypics-upload-dropzone {
	height: 400px;
	margin-top: 10px;
	width: 99%;
	position: relative;
	overflow-y: scroll;
}

div.tidypics-upload-dropzone-droppable {
	border: 2px dashed #CCCCCC;
	border-radius: 3px 3px 3px 3px;
	-moz-border-radius: 3px 3px 3px 3px;
	-webkit-border-radius: 3px 3px 3px 3px;
}

div.tidypics-upload-dropzone-droppable.tidypics-upload-dropzone-drag {
	background: #EEEEEE;
}

div.tidypics-upload-dropzone-inner {
	position: absolute;
	top: 50%;
	height: 150px;
	width: 100%;
	margin-top: -75px;
	color: #888888;
	text-align: center;
}

input.tidypics-upload-finish-input {
	display: none;
}

div.tidypics-upload-status {
	color: #777777;
	font-weight: bold;
	font-size: 1.2em;
}

div.tidypics-upload-status-error {
	color: red;
}

div.tidypics-upload-image-element {
	background: none repeat scroll 0 0 #EEEEEE;
	border: 1px solid #BBBBBB;
	float: left;
	margin: 2px;
	overflow: hidden;
	padding: 5px;
	height: 130px;
	width: 138px;
	position: relative;
}

div.tidypics-upload-image-element .tidypics-upload-image-progress {
	border: 1px solid #AAAAAA;
	height: 16px;
	width: 134px;
	position: absolute;
	bottom: 5px;
	padding: 1px;
}

div.tidypics-upload-image-element .tidypics-upload-image-progress .tidypics-upload-image-progress-bar {
	height: 16px;
	background: darkred;
	width: 0px;
}

div.tidypics-upload-image-element .tidypics-upload-image-error-header {
	font-size: 12px;
	font-weight: bold;
	color: #888;
}


div.tidypics-upload-image-element .tidypics-upload-image-errors {
	font-size: 11px;
	font-weight: bold;
	color: red;
}

div.tidypics-upload-image-element .tidypics-upload-image-name {
	color: #999999;
	font-weight: bold;
	height: 20px;
	margin-top: 45px;
	overflow: hidden;
	text-align: center;
}

div.tidypics-upload-image-element img.tidypics-upload-image-thumbnail {
	display: block;
	height: 122px;
	margin-left: auto;
	margin-right: auto;
	text-align: center;
}

input.tidypics-upload-new-album-title,
select.tidypics-upload-select-existing-album {
	width: 380px;
}

.elgg-menu-item-album-info a {
	color: #FFFFFF !important;
}

/* Upload menus */
.elgg-menu-tidypics-upload-album-metadata {
	margin-top: 3px;
}

.elgg-menu-tidypics-upload-album li,
.elgg-menu-tidypics-upload-album-metadata li {
	margin-right: 5px;
}

.elgg-menu-item-album-label span {
	margin-right: 5px;
}

.elgg-menu-item-album-label,
.elgg-menu-item-album-tags-label,
.elgg-menu-item-album-access-label {
	color: #555555;
	font-weight: bold;
}

/* ***************************************
	Photo listing filter menu
*************************************** */

.elgg-menu-photos-listing-filter,
.elgg-menu-photos-listing-sort {
	padding-bottom: 5px;
	border-bottom: 1px dotted #CCC;
}

.elgg-menu-photos-listing-sort {
	text-align: center;
	padding-top: 5px;
}

.elgg-menu-photos-listing-filter li label,
.elgg-menu-photos-listing-sort li label {
	font-size: 92%;
    margin-right: 5px;
    margin-left: 5px;
    text-transform: uppercase;
    color: #555555;
}

.elgg-menu-photos-listing-filter li input,
.elgg-menu-photos-listing-sort li input {
	font-size: 90%;
	height: 24px;
	width: 92px;
}

.elgg-menu-item-tidypics-list-sort-order a {
	margin-bottom: 1px;
}

/* ***************************************
	Photo lightbox
*************************************** */

.tidypics-lightbox-wrap {
    height: 100% !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
}

.tidypics-lightbox-wrap .fancybox2-skin {
	height: 100% !important;
	width: 100% !important;
	padding: 0 !important;
	-webkit-border-radius: 0;
	-moz-border-radius: 0;
	border-radius: 0;
}

.tidypics-lightbox-wrap .fancybox2-outer {
	background: #000000;
	height: 100%;
}

.tidypics-lightbox-wrap .fancybox2-inner {
	width: 100% !important;
	height: 100% !important;
}

/* Lightbox inner content */
.tidypics-lightbox-container {
	display: table;
	height: 100%;
	width: 100%;
}

.tidypics-lightbox-container .tidypics-lightbox-header {
	display: table-row;
	height: 60px;
}

.tidypics-lightbox-container .tidypics-lightbox-header .tidypics-lightbox-keys-legend {
	color: #CCCCCC;
	float: left;
	font-size: 13px;
	padding: 16px 40px 15px 15px;
	width: 350px;
}

.tidypics-lightbox-container .tidypics-lightbox-header .elgg-menu-entity > li > a {
	color: #AAAAAA;
}

.tidypics-lightbox-container .tidypics-lightbox-header .tidypics-lightbox-header-metadata {
	float: right;
    padding: 16px 40px 15px 15px;
}

.tidypics-lightbox-container .tidypics-lightbox-header a.tidypics-lightbox-close {
	float: right;
	margin-left: 15px;
    margin-top: 2px;
    font-weight: bolder;
    color: #FFFFFF;
}

.tidypics-lightbox-container .tidypics-lightbox-header a.tidypics-lightbox-close .fancybox2-close {
	right: 14px;
    top: 12px;
}

.tidypics-lightbox-container .tidypics-lightbox-header a.tidypics-lightbox-close:hover {
	cursor: pointer;
	text-decoration: none;
}

.tidypics-lightbox-container .tidypics-lightbox-middle {
	display: table;
	width: 100%;
	height: 100%;
}

.tidypics-lightbox-container .tidypics-lightbox-middle .tidypics-lightbox-middle-container {
	display: table-cell;
}

.tidypics-lightbox-container .tidypics-lightbox-middle .tidypics-lightbox-sidebar {
	display: table-cell;
	width: 475px;
	vertical-align: top;
}

.tidypics-lightbox-container .tidypics-lightbox-middle .tidypics-lightbox-sidebar .tidypics-lightbox-photo-title {
	border-bottom: 1px solid #CCCCCC;
	margin-bottom: 5px;
	position: relative;
}

.tidypics-lightbox-container .tidypics-lightbox-middle .tidypics-lightbox-sidebar .tidypics-lightbox-photo-description {
	margin-left: 10px;
	position: relative;
}

.tidypics-lightbox-container .tidypics-lightbox-middle .tidypics-lightbox-sidebar .tidypics-lightbox-photo-tags {
	position: relative;
	padding-bottom: 6px;
}

.tidypics-lightbox-container .tidypics-lightbox-middle .tidypics-lightbox-sidebar .none {
	color: #666666;
}

.tidypics-lightbox-container .tidypics-lightbox-middle .tidypics-lightbox-sidebar .tidypics-lightbox-other {
	margin: 10px;
	color: #666666;
	border-top: 1px solid #DDDDDD;
	padding-top: 9px;
}

.tidypics-lightbox-container .tidypics-lightbox-footer {
	height: 60px;
    position: fixed;
    width: 100%;
    bottom: 0;
}

.tidypics-lightbox-container .tidypics-photo {
	display: none;
	min-height: 50px;
}

.tidypics-lightbox-container .tidypics-photo-wrapper {
	padding-top: 25px;
	padding-bottom: 25px;
}

.tidypics-lightbox-container .tidypics-lightbox-sidebar-content {
	background: none repeat scroll 0 0 #FFFFFF;
	-moz-border-radius: 5px 5px 5px 5px;
	-webkit-border-radius: 5px 5px 5px 5px;
    border-radius: 5px 5px 5px 5px;
    margin: 10px;
    padding: 5px;
    width: 435px;
    overflow-y: auto;
}

.tidypics-lightbox-container .tidypics-lightbox-can-edit:hover .tidypics-lightbox-edit-overlay {
	display: block;
}

.tidypics-lightbox-container .tidypics-lightbox-edit-overlay {
	display: none;
	position: absolute;
    right: 0;
    top: 0;
}

.tidypics-lightbox-container .tidypics-lightbox-edit-overlay a {
	background: #FFFFFF;
	border: 1px solid #BBBBBB;
	padding: 2px;
	width: 40px;
	text-align: center;
	display: block;
	text-decoration: none;
	-webkit-box-shadow: 1px 1px 5px #CCC;
	-moz-box-shadow: 1px 1px 5px #CCC;
	box-shadow: 1px 1px 5px #CCC;
}

.tidypics-lightbox-container .tidypics-lightbox-edit-title {
	width: 65%;
	margin-bottom: 2px;
}

.tidypics-lightbox-container .tidypics-lightbox-edit-description {
	margin-bottom: 5px;
	height: 150px;
}

.tidypics-lightbox-container .tidypics-lightbox-edit-tags {
	margin-bottom: 7px;
    margin-top: 3px;
}

/* ***************************************
	Move to album lightbox
*************************************** */

#tidypics-move-to-album-lightbox {
	width: 450px;
	overflow: hidden;
}

#tidypics-move-to-album-lightbox .elgg-image-block {
	border-bottom: 1px dotted #DDDDDD;
	margin: 4px 0;
}

#tidypics-move-to-album-lightbox .elgg-image-block:first-child {
	border-top: 1px dotted #DDDDDD;
	padding-top: 5px;
}

#tidypics-move-to-album-lightbox .elgg-photo {
	height: 40px;
	padding: 2px;
	width: 40px;
}

#tidypics-move-to-album-lightbox .elgg-image-block .elgg-body {
	color: #444444;
	font-size: 14px;
	font-weight: bold;
	padding-top: 15px;
}

#tidypics-move-to-album-lightbox .elgg-image-block .elgg-image {
	padding-top: 14px;
}


/* ***************************************
	Keyboard cues
*************************************** */

#fancybox2-buttons .light-keys {
	
}

/* ***************************************
	Keys.css (see vendors)
*************************************** */


/* Base style, essential for every key. */
kbd, .key {
	display: inline;
	display: inline-block;
	min-width: 1em;
	padding: .2em .3em;
	font: normal .85em/1 "Lucida Grande", Lucida, Arial, sans-serif;
	text-align: center;
	text-decoration: none;
	-moz-border-radius: .3em;
	-webkit-border-radius: .3em;
	border-radius: .3em;
	border: none;
	cursor: default;
	-moz-user-select: none;
	-webkit-user-select: none;
	user-select: none;
}
kbd[title], .key[title] {
	cursor: help;
}

/* Dark style for display on light background. This is the default style. */
kbd, kbd.dark, .dark-keys kbd, .key, .key.dark, .dark-keys .key {
	background: rgb(80, 80, 80);
	background: -moz-linear-gradient(top, rgb(60, 60, 60), rgb(80, 80, 80));
	background: -webkit-gradient(linear, left top, left bottom, from(rgb(60, 60, 60)), to(rgb(80, 80, 80)));
	color: rgb(250, 250, 250);
	text-shadow: -1px -1px 0 rgb(70, 70, 70);
	-moz-box-shadow: inset 0 0 1px rgb(150, 150, 150), inset 0 -.05em .4em rgb(80, 80, 80), 0 .1em 0 rgb(30, 30, 30), 0 .1em .1em rgba(0, 0, 0, .3);
	-webkit-box-shadow: inset 0 0 1px rgb(150, 150, 150), inset 0 -.05em .4em rgb(80, 80, 80), 0 .1em 0 rgb(30, 30, 30), 0 .1em .1em rgba(0, 0, 0, .3);
	box-shadow: inset 0 0 1px rgb(150, 150, 150), inset 0 -.05em .4em rgb(80, 80, 80), 0 .1em 0 rgb(30, 30, 30), 0 .1em .1em rgba(0, 0, 0, .3);
}

/* Light style for display on dark background. */
kbd.light, .light-keys kbd, .key.light, .light-keys .key {
	background: rgb(250, 250, 250);
	background: -moz-linear-gradient(top, rgb(210, 210, 210), rgb(255, 255, 255));
	background: -webkit-gradient(linear, left top, left bottom, from(rgb(210, 210, 210)), to(rgb(255, 255, 255)));
	color:  rgb(50, 50, 50);
	text-shadow: 0 0 2px rgb(255, 255, 255);
	-moz-box-shadow: inset 0 0 1px rgb(255, 255, 255), inset 0 0 .4em rgb(200, 200, 200), 0 .1em 0 rgb(130, 130, 130), 0 .11em 0 rgba(0, 0, 0, .4), 0 .1em .11em rgba(0, 0, 0, .9);
	-webkit-box-shadow: inset 0 0 1px rgb(255, 255, 255), inset 0 0 .4em rgb(200, 200, 200), 0 .1em 0 rgb(130, 130, 130), 0 .11em 0 rgba(0, 0, 0, .4), 0 .1em .11em rgba(0, 0, 0, .9);
	box-shadow: inset 0 0 1px rgb(255, 255, 255), inset 0 0 .4em rgb(200, 200, 200), 0 .1em 0 rgb(130, 130, 130), 0 .11em 0 rgba(0, 0, 0, .4), 0 .1em .11em rgba(0, 0, 0, .9);
}