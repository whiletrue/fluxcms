<?php
// +----------------------------------------------------------------------+
// | popoon                                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Christian Stocker <chregu@bitflux.ch>                        |
// +----------------------------------------------------------------------+
//
// $Id: options.php 6188 2006-01-03 10:09:08Z chregu $

/**
* Deprecated, use globals instead
*
* @author   Christian Stocker <chregu@bitflux.ch>
* @version  $Id: options.php 6188 2006-01-03 10:09:08Z chregu $
* @package  popoon
* @module	schemes_options
* @deprecated  since almost the beginning, use globals:// instead
*/

function scheme_options($value)
{
	return popoon_sitemap::getGlobalOptions($value);
}
	
