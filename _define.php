<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
# This file is part of Ductile, a theme for Dotclear
#
# Copyright (c) 2011 - Association Dotclear
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */				"Da Scritch 2013",
	/* Description*/		"Da Scritch 2013 theme",
	/* Author */			"dascritch.net d'après Ductile",
	/* Version */			'4.0',
	/* Properties */		[
								'standalone_config' => true,
								'requires' => [['core', '2.24']]
							]
);
?>