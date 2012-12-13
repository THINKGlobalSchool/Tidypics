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
	Upload items
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