<?php
/*
 * todo: вынести реквизиты в настройки
 */
return array (
			'class' => 'SenterComponent',
			'clientDrivers' => array(
				'zendesk' => array (
					'class' => 'SenterZendeskDriver',
				),
			),
            'devDriver' => array (
                'class' => 'SenterGitHubDriver',
                'login' => 'stepanoff',
                'password' => 'ghjntrn123',
            ),
		);
?>