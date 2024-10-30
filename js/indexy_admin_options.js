/*
 * Indexy - a WordPress plugin to manage glossary pages.
 *
 * Visit the plugin's webpage at http://www.damnleet.com/indexy
 *
 * Copyright 2015 Jeroen Treurniet (contact@damnleet.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Highlighting repeat pattern fields.
jQuery("#indexy_highlight_repeat_off, #indexy_highlight_repeat_on, #indexy_highlight_repeat_all").change(function(event)
{
	if( jQuery("#indexy_highlight_repeat_on").prop('checked') )
	{
		jQuery("#indexy_highlight_repeat_count").removeAttr('disabled');
		jQuery("#indexy_highlight_repeat_enable_skip").removeAttr('disabled');

		if( jQuery("#indexy_highlight_repeat_enable_skip").prop('checked') )
		{
			jQuery("#indexy_highlight_repeat_skip").removeAttr('disabled');
		}
	}
	else
	{
		jQuery("#indexy_highlight_repeat_count").attr( 'disabled', 'disabled' );
		jQuery("#indexy_highlight_repeat_enable_skip").attr( 'disabled', 'disabled' );
		jQuery("#indexy_highlight_repeat_skip").attr( 'disabled', 'disabled' );
	}
});

// Highlighting repeat enable field.
jQuery("#indexy_highlight_repeat_enable_skip").change(function(event)
{
	if( jQuery("#indexy_highlight_repeat_enable_skip").prop('checked') )
	{
		jQuery("#indexy_highlight_repeat_skip").removeAttr('disabled');
	}
	else
	{
		jQuery("#indexy_highlight_repeat_skip").attr( 'disabled', 'disabled' );
	}
});

// AJAX handler to close the welcome panel.
jQuery(".welcome-panel-close").click(function()
{
	jQuery.post
	(
		ajaxurl,
		{
			action:						'indexy_welcome_panel_close_action',
			welcomepanelnonce_indexy:	jQuery("#welcomepanelnonce_indexy").val()
		},
		function(data)
		{
			if( data == 1 )
			{
				jQuery("#welcome-panel").hide(400);
			}
		}
	);
	return false;
});
