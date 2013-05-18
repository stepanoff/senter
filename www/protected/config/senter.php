<?php
/*
 * todo: вынести реквизиты в настройки
 */
return array (
			'class' => 'SenterComponent',
			'clientDrivers' => array(
				'zendesk' => array (
					'class' => 'SenterZendeskDriver',
                    'user' => $params['zendesk']['user'],
                    'apiUrl' => $params['zendesk']['apiUrl'],
                    'apiKey' => $params['zendesk']['apiKey'],
				),
			),
            'devDriver' => array (
                'class' => 'SenterGitHubDriver',
                'login' => $params['github']['login'],
                'password' => $params['github']['password'],
                'githubUser' => $params['github']['githubUser'],
            ),
		);
?>