<?php
/**
 * Tidypics CSS
 *
 * @author Cash Costello
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2
 */
?>

/* ***************************************
	TIDYPICS
*************************************** */
.elgg-module-tidypics-album,
.elgg-module-tidypics-image,
.elgg-module-tidypics-upload {
	width: 161px;
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

.tidypics-photo-item + .tidypics-photo-item {
	margin-left: 7px;
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
	width: 250px;
	margin-left: -125px;
	text-align: center;
}

.tidypics-tagging-select {
	position: absolute;
	max-width: 300px;
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
	float: left;
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