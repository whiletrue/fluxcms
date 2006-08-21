<?xml version="1.0"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xhtml="http://www.w3.org/1999/xhtml" 
    xmlns:i18n="http://apache.org/cocoon/i18n/2.1"
    xmlns="http://www.w3.org/1999/xhtml"
    > 
    <xsl:param name="webroot" value="'/'"/>
    <xsl:variable name="pluginName" select="/bx/plugin/@name"/>
     
     <xsl:template match="/">
		<html>
		<head>
			<link type="text/css" href="http://fluxcms/themes/standard/admin/css/formedit.css" rel="stylesheet"/>
			<xsl:text>
			</xsl:text>
			<link type="text/css" href="http://fluxcms/themes/standard/admin/css/blog.css" rel="stylesheet"/>
			<xsl:text>
			</xsl:text>
			
			<script type="text/javascript" src="http://fluxcms/webinc/plugins/blog/common.js"></script>
			<xsl:text>
			</xsl:text>
			
		</head>
		<body>
			<xsl:apply-templates mode="xhtml"/>
		</body>
		</html>
     </xsl:template>
     
	 <xsl:template match="/bx/plugin[@name='admin_users']/useradministration/users" mode="xhtml">
	 	<h2 class="userPage">
			Users
		</h2>
		<div id='users'>
		<table cellpadding="5" border="0" class="bigUglyEditTable" id="posts">
		<tr>
			<th>
			</th>
			<th style="width:100px;">
				Username
			</th>
			<th style="width:100px;">
				Fullname
			</th>
			<th style="width:100px;">
				Mail
			</th>
		</tr>
		<xsl:for-each select="user">
			<tr>
			
			<xsl:choose>
            <xsl:when test="position() mod 2= 0">
            <xsl:attribute name="class">uneven</xsl:attribute>
            </xsl:when>
            </xsl:choose>
			
				<td style="width:20px;">
					<a href="?delete={id}">
						<img style='border:0px;' src='http://fluxcms/admin/webinc/img/icons/delete.gif'/> 
					</a>
				</td>
				<td style="width:80px;">
					<a href="edit/?id={id}"><xsl:value-of select="username"/></a>
				</td>
				<td style="width:80px;">
					<xsl:value-of select="fullname"/>
				</td>
				<td style="width:80px;">
					<xsl:value-of select="mail"/>
				</td>
			</tr>
		</xsl:for-each>
		</table>
		<br/>
		<a href="{$webroot}admin/users/edit/?add=1">
			<input type="button" value="Add a new user"/>
		</a>
		
		</div>
	 </xsl:template>
	 
	 <xsl:template match="/bx/plugin[@name='admin_users']/useradministration/new" mode="xhtml">
	 	<h2 class="userPage">
			New User
		</h2>
		<div id='userdiv'>
		
		<form name="adminform" action="" method="POST" enctype="multipart/form-data">
		<ul>
		<h3 class="userPage">
			General
		</h3>
		<li>
			 Username<br/>
			<input type="text" value="{username}" name="bx[plugins][admin_users][username]"/>
		</li>
		<li>
			 Fullname<br/>
		
			<input type="text" value="{fullname}" name="bx[plugins][admin_users][fullname]"/>
		</li>
		<li>
			 Mail Adress<br/>
		
			<input type="text" value="{mail}" name="bx[plugins][admin_users][mail]"/>
		</li>
		<li>
			 Guip<br/>
		
			<input type="text" value="{user_gupi}" name="bx[plugins][admin_users][gupi]"/>
		</li>
		<li>
			 Gid<br/>
		
			<input type="text" value="{user_gid}" name="bx[plugins][admin_users][gid]"/>
		</li>
		<li>
			 Sprache<br/>
		
			<input type="text" value="{user_adminlang}" name="bx[plugins][admin_users][lang]"/>
		</li>
		<li>
		<br/>
		<img src="http://fluxcms/admin/webinc/img/closed_klein.gif" id="advanced_triangle" onclick="toggleUserAdvanced();"/>
		More options (click to expand)
		<br/><br/>
		</li>
		<div id="user" style="display:none;">
		<h3 class="userPage">
			Plazes
		</h3>
		<li>
			 Plazes Username<br/>
		
			<input type="text" value="{plazes_username}" name="bx[plugins][admin_users][plazes_username]"/>
		</li>
		<li>
			 Plazes Password<br/>
		
			<input type="text" value="{plazes_password}" name="bx[plugins][admin_users][plazes_pwd]"/>
		</li>
		<h3 class="userPage">
			Authservices
		</h3>
		<li>
			 <xsl:for-each select="/bx/plugin[@name='admin_users']/useradministration/authservices/authservice">
			 	<xsl:value-of select="."/><br/>
				<input type="text" name="bx[plugins][admin_users][{.}]" />
				<br/>
			</xsl:for-each>
		</li>
		</div>
		
			<hr/>
		
		<li>
			 New Password<br/>
		
			<input type="text" value="{plazes_password}" name="bx[plugins][admin_users][new_pwd]"/>
		</li>
		<li>
			 Retype New Password<br/>
		
			<input type="text" value="{plazes_password}" name="bx[plugins][admin_users][new_pwd_re]"/>
		</li>
		<li style="color:red;">
			 Your Password<br/>
		
			<input type="text" value="{plazes_password}" name="bx[plugins][admin_users][pwd]"/>
			<br/><br/>
		</li>
		<li>
			<input type="submit" value="Add" name="bx[plugins][admin_users][add]"/>
		
			<a href="{$webroot}admin/users/">
			<input type="button" value="Back"/>
			</a>
		</li>
		</ul>
		</form>
		</div>
	 </xsl:template>
	 
	 <xsl:template match="/bx/plugin[@name='admin_users']/useradministration/user" mode="xhtml">
	 <h2 class="userPage">
			User | <xsl:value-of select="username"/>
		</h2>
		<div id='usersdiv'>
		<h3 class="userPage">
			General
		</h3>
		<form name="adminform" action="" method="POST" enctype="multipart/form-data">
		<ul>
		<li>
			Username<br/>
			<input type="text" value="{username}" name="bx[plugins][admin_users][username]"/>
		</li>
		<li>
			Fullname<br/>
			<input type="text" value="{fullname}" name="bx[plugins][admin_users][fullname]"/>
		</li>
		<li>
			Mail Adress<br/>
			<input type="text" value="{mail}" name="bx[plugins][admin_users][mail]"/>
		</li>
		<li>
			Guip<br/>
			<input type="text" value="{user_gupi}" name="bx[plugins][admin_users][gupi]"/>
		</li>
		<li>
			Gid<br/>
			<input type="text" value="{user_gid}" name="bx[plugins][admin_users][gid]"/>
		</li>
		<li>
			Sprache<br/>
			<input type="text" value="{user_adminlang}" name="bx[plugins][admin_users][lang]"/>
		</li>
		
		<li>
		<img src="http://fluxcms/admin/webinc/img/closed_klein.gif" id="advanced_triangle" onclick="toggleUserAdvanced();"/>
		More options (click to expand)
		<br/><br/>
		</li>
		<div id="user" style="display:none;">
		<h3 class="userPage">
			Plazes
		</h3>
		<li>
			 Plazes Username<br/>
		
			<input type="text" value="{plazes_username}" name="bx[plugins][admin_users][plazes_username]"/>
		</li>
		<li>
			 Plazes Password<br/>
		
			<input type="text" value="{plazes_password}" name="bx[plugins][admin_users][plazes_pwd]"/>
		</li>
		<h3 class="userPage">
			Authservices
		</h3>
		<li>
			 <xsl:for-each select="/bx/plugin[@name='admin_users']/useradministration/user/services/service">
			 	<xsl:value-of select="servicename"/><br/>
				<input type="text" name="bx[plugins][admin_users][{servicename}]" value="{account}" />
				<br/>
			</xsl:for-each>
		</li>
		</div>
		
			<hr/>
		
		
		<li>
			New Password
		<br/>
			<input type="text" value="{password}" name="bx[plugins][admin_users][new_pwd]"/>
		</li>
		<li>
			Retype New Password
		<br/>
			<input type="text" value="{password}" name="bx[plugins][admin_users][new_pwd_re]"/>
		</li>
		<li style="color:red;">
			Password
		<br/>
			<input type="text" value="{password}" name="bx[plugins][admin_users][pwd]"/>
		</li>
		<li>
			<input type="submit" value="Send"/>
		
			<a href="{$webroot}admin/users/">
			<input type="button" value="Back"/>
			</a>
		</li>
		</ul>
		</form>
		</div>
	 </xsl:template>
	 
</xsl:stylesheet>
