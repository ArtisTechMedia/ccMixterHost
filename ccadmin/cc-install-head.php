<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: cc-install-head.php 8953 2008-02-11 21:44:18Z fourstones $
*
*/
?>
<head>
    <title><?=$install_title?></title>
    <style type="text/css">
    body, td
    {
        font-family: arial;
        background-color: white;
        color: black;
    }
    .fh
    {
        text-align: right;
        font-weight: bold;
        font-family: Verdana;
        padding-bottom: 12px;
        vertical-align: top;
        width: 25%;
        background-color: #ddd;
    }
    .fv
    {
        vertical-align: top;
        text-align: left;
        padding-bottom: 12px;
    }
    .rq, .rqmsg
    {
        color: brown;
    }
    .rqmsg
    {
        border: 1px dotted brown;
        padding: 4px;
        margin: 0px 10% 0px 10%;
        text-align:center;
    }
    input[type='text']
    {
        font-family: Verdana;
        width: 250px;
        font-size: smaller;
    }
    .ft
    {
        font-size: smaller;
        padding-bottom: 12px;
        vertical-align: top;
        font-weight: normal;
    }
    .fe
    {
        font-weight: bold;
        color: red;
    }
    .d
    {
        font-family: Courier New, courier, serif;
    }
    .err
    {
        border: 1px solid red;
        color: red;
        font-weight: bold;
        padding: 4px;
        width: 80%;
        margin: 8px;
    }
    .ini_table .c
    {
        text-align: center;
    }
    .ini_table .r
    {
        text-align: right;
    }
    .ini_table td
    {
       padding: 3px;
       vertical-align: top;
    }
    .ini_table th
    {
        border-bottom: solid 1px #999;
    }
    .file_name
    {
        font-family:Courier New, courier, serif;
        font-size:smaller;
    }
    </style>
</head>
<body style="margin:3% 11% 3% 4%">
<h1><?=$install_title?></h1>
