<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--
Copyright 2004 ThoughtWorks, Inc

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
-->
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1"
 http-equiv="content-type">
  <title>Test Open</title>
</head>
<body>

<?php

$fix = substr(md5(time()),0,6);

?>
<table cellpadding="1" cellspacing="1" border="1">
  <tbody>
    <tr>
      <td rowspan="1" colspan="3">Test Open<br>
      </td>
    </tr>
	<tr>
      <td>open</td>
      <td>/admin/?logout</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>open</td>
      <td>/admin</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>type</td>
      <td>username</td>
      <td>admin</td>
    </tr>
	<tr>
      <td>type</td>
      <td>password</td>
      <td>minerva</td>
    </tr>
	<tr>
      <td>clickAndWait</td>
      <td>submitButton</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>pause</td>
      <td>10000</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>selectWindow</td>
      <td>edit</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>clickAndWait</td>
      <td>//a[@title='new_post']</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>pause</td>
      <td>10000</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>type</td>
      <td>title</td>
      <td>test <?php echo $fix;?></td>
    </tr>
	<tr>
      <td>type</td>
      <td>uri</td>
      <td>test-<?php echo $fix;?></td>
    </tr>
	<tr>
      <td>click</td>
      <td>bx[plugins][admin_edit][categories][General]</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>clickAndWait</td>
      <td>//input[@value='Save']</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>selectWindow</td>
      <td>null</td>
      <td>&nbsp;</td>
    </tr>
	
		<tr>
      <td>selectWindow</td>
      <td>header</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>click</td>
      <td>logout</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>selectWindow</td>
      <td>null</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>open</td>
      <td>/blog</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
		<td>assertTextPresent</td>
		<td>test <?php echo $fix;?></td>
		<td>&nbsp;</td>
	</tr> 
	<tr>
      <td>open</td>
      <td>/admin</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>type</td>
      <td>username</td>
      <td>admin</td>
    </tr>
	<tr>
      <td>type</td>
      <td>password</td>
      <td>minerva</td>
    </tr>
	<tr>
      <td>clickAndWait</td>
      <td>submitButton</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>pause</td>
      <td>10000</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>selectWindow</td>
      <td>edit</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>clickAndWait</td>
      <td>//a[@title='overview']</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>clickAndWait</td>
      <td>//a[@href='./test-<?php echo $fix;?>.html']</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>pause</td>
      <td>10000</td>
      <td>&nbsp;</td>
    </tr>
	<tr>
      <td>clickAndWait</td>
      <td>//input[@value='Delete']</td>
      <td>&nbsp;</td>
    </tr>
	<!--tr>
      <td>fireEvent</td>
      <td>submit</td>
      <td>submitButton</td>
    </tr>
	<tr>
	<td>open</td>
	<td>javascript:service()</td>
	<td></td>
	</tr--> 
	
   </tbody>
</table>

</body>
</html>
