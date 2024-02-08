<?php
/*
** Zabbix
** Copyright (C) 2001-2024 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


require_once dirname(__FILE__).'/../../include/CWebTest.php';
require_once dirname(__FILE__).'/../behaviors/CMessageBehavior.php';

/**
 * @backup scripts
 *
 * @onBefore prepareData
 */
class testManualActionScripts extends CWebTest {

	/**
	 * Id of host.
	 *
	 * @var array
	 */
	protected static $hostid;

	/**
	 * Attach MessageBehavior to the test.
	 *
	 * @return array
	 */
	public function getBehaviors() {
		return [CMessageBehavior::class];
	}

	public function prepareData() {
		// Create host and trapper item for manual user input test.
		$host = CDataHelper::createHosts([
			[
				'host' => 'A host for scripts check',
				'interfaces' => [
					[
						'type' => INTERFACE_TYPE_AGENT,
						'main' => INTERFACE_PRIMARY,
						'useip' => INTERFACE_USE_IP,
						'ip' => '127.1.9.1',
						'dns' => '',
						'port' => '10777'
					]
				],
				'groups' => [
					'groupid' => '19' // Applications.
				],
				'items' => [
					[
						'name' => 'Scripts trapper',
						'key_' => 'script_trap',
						'type' => ITEM_TYPE_TRAPPER,
						'value_type' => ITEM_VALUE_TYPE_UINT64
					]
				]
			]
		]);
		self::$hostid = $host['hostids']['A host for scripts check'];

		CDataHelper::call('trigger.create', [
			[
				'description' => 'Attention: script execution is needed',
				'expression' => 'last(/A host for scripts check/script_trap)<>0',
				'type' => 1,
				'priority' => TRIGGER_SEVERITY_WARNING
			]
		]);

		// Create problem for manual event action check.
		CDBHelper::setTriggerProblem('Attention: script execution is needed', TRIGGER_VALUE_TRUE);
	}

	public function getManualInputData() {
		return [
			// #0 Host url with {MANUALINPUT} macro, confirmation message and input type - string.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host url with {MANUALINPUT} macro, confirmation message and input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter host id',
						'Default input string' => '1',
						'Input validation rule' => '\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 1-99999 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Host id {MANUALINPUT} is selected. Proceed?'
					],
					'manualinput' => '0',
					'prompt' => 'Enter host id',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9])\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #1 Event url with {MANUALINPUT} macro, confirmation message and input type - string.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event url with {MANUALINPUT} macro, confirmation message and input type - string',
						'Scope' => 'Manual event action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter host id',
						'Default input string' => '1',
						'Input validation rule' => '\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 1-99999 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Host id {MANUALINPUT} is selected. Proceed?'
					],
					'manualinput' => '0',
					'prompt' => 'Enter host id',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9])\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #2 Event url without confirmation message (input type - string).
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event url with without confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter host id',
						'Default input string' => '1',
						'Input validation rule' => '\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 1-99999 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => '999999',
					'prompt' => 'Enter host id',
					'event' => 'Inheritance trigger with tags',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9])\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #3 Host url without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host url with {MANUALINPUT} macro and without confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter host id',
						'Default input string' => '1',
						'Input validation rule' => '\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 1-99999 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => '0',
					'prompt' => 'Enter host id',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9])\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #4 Host webhook without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Host webhook without confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b', // regex 1-9 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => 'a',
					'prompt' => 'Enter value for parameter A',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: \b[1-9]\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #5 Event webhook without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Event webhook without confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b', // regex 1-9 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => '10',
					'prompt' => 'Enter value for parameter A',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: \b[1-9]\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #6 Host webhook with confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Host webhook with confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b', // regex 1-9 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Parameter A will contain value => {MANUALINPUT}. Proceed?'
					],
					'manualinput' => '10',
					'prompt' => 'Enter value for parameter A',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: \b[1-9]\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #7 Event webhook with confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Event webhook with confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b', // regex 1-9 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Parameter A will contain value => {MANUALINPUT}. Proceed?'
					],
					'manualinput' => '',
					'prompt' => 'Enter value for parameter A',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: \b[1-9]\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #8 Host script with {MANUALINPUT} macro, confirmation message and input type - string.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host script with {MANUALINPUT} macro and confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'Script',
						'Commands' => 'ping -c {MANUALINPUT} {HOST.HOST};',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter 🚩{HOST.HOST}🚩 ping count',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b',
						'Enable confirmation' => true,
						'Confirmation text' => 'Ping count: {MANUALINPUT}'
					],
					'manualinput' => '0',
					'prompt' => 'Enter 🚩A host for scripts check🚩 ping count',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: \b[1-9]\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #9 Event script with {MANUALINPUT} macro, confirmation message and input type - string.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event script with {MANUALINPUT} macro and confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'Script',
						'Commands' => 'ping -c {MANUALINPUT} {HOST.HOST};',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter 🚩{HOST.HOST}🚩 ping count',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b',
						'Enable confirmation' => true,
						'Confirmation text' => 'Ping count: {MANUALINPUT}'
					],
					'manualinput' => '0',
					'prompt' => 'Enter 🚩A host for scripts check🚩 ping count',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: \b[1-9]\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #10 Event script without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event script without confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'Script',
						'Commands' => 'ping -c {MANUALINPUT} {HOST.HOST};',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter 🚩{HOST.HOST}🚩 ping count',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b',
						'Enable confirmation' => false
					],
					'manualinput' => '10',
					'prompt' => 'Enter 🚩A host for scripts check🚩 ping count',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: \b[1-9]\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #11 Host script without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host script without confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'Script',
						'Commands' => 'ping -c {MANUALINPUT} {HOST.HOST};',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter 🚩{HOST.HOST}🚩 ping count',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b',
						'Enable confirmation' => false
					],
					'manualinput' => '',
					'prompt' => 'Enter 🚩A host for scripts check🚩 ping count',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: \b[1-9]\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #12 Host SSH without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host SSH without confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter hostname',
						'Default input string' => 'Aa',
						'Input validation rule' => '[A-Za-z]', // all letters (uppercase and lowercase).
						'Enable confirmation' => false
					],
					'manualinput' => '11',
					'prompt' => 'Enter hostname',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: [A-Za-z].',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #13 Host SSH with confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host SSH with confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter hostname',
						'Default input string' => 'Aa',
						'Input validation rule' => '[A-Za-z]', // all letters (uppercase and lowercase).
						'Enable confirmation' => true,
						'Confirmation text' => 'Hostname is {MANUALINPUT}'
					],
					'manualinput' => '.',
					'prompt' => 'Enter hostname',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: [A-Za-z].',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #14 Event SSH without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event SSH without confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter hostname',
						'Default input string' => 'Aa',
						'Input validation rule' => '[A-Za-z]', // all letters (uppercase and lowercase).
						'Enable confirmation' => false
					],
					'manualinput' => '?',
					'prompt' => 'Enter hostname',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: [A-Za-z].',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #15 Event SSH with confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event SSH with confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter hostname',
						'Default input string' => 'Aa',
						'Input validation rule' => '[A-Za-z]', // all letters (uppercase and lowercase).
						'Enable confirmation' => true,
						'Confirmation text' => 'Hostname is {MANUALINPUT}'
					],
					'manualinput' => '',
					'prompt' => 'Enter hostname',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: [A-Za-z].',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #16 Host Telnet without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host Telnet without confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter port',
						'Default input string' => '22',
						'Input validation rule' => '\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 10-99999 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => '1',
					'prompt' => 'Enter port',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9])\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #17 Host Telnet with confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host Telnet with confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter port',
						'Default input string' => '22',
						'Input validation rule' => '\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 10-99999 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Selected port:{MANUALINPUT}. Proceed?'
					],
					'manualinput' => '.',
					'prompt' => 'Enter port',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9])\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #18 Event Telnet without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event Telnet without confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter port',
						'Default input string' => '22',
						'Input validation rule' => '\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 10-99999 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => '?',
					'prompt' => 'Enter port',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9])\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #19 Event Telnet with confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event Telnet with confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter port',
						'Default input string' => '22',
						'Input validation rule' => '\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 10-99999 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Selected port:{MANUALINPUT}. Proceed?'
					],
					'manualinput' => '',
					'prompt' => 'Enter port',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-9][0-9][0-9][0-9][0-9])\b.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #20 Host IPMI without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host IPMI without confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'IPMI',
						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P {MANUALINPUT} -L user sensor',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
								'one digit, one special character and minimum eight in length',
						'Default input string' => 'Ex@mple7',
						'Input validation rule' => '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
						'Enable confirmation' => false
					],
					'manualinput' => 'example1',
					'prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
							'one digit, one special character and minimum eight in length',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #21 Host IPMI with confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Host IPMI with confirmation message',
						'Scope' => 'Manual host action',
						'Type' => 'IPMI',
						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P {MANUALINPUT} -L user sensor',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
								'one digit, one special character and minimum eight in length',
						'Default input string' => 'Ex@mple7',
						'Input validation rule' => '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
						'Enable confirmation' => true,
						'Confirmation text' => 'Are you sure?'
					],
					'manualinput' => '.',
					'prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
							'one digit, one special character and minimum eight in length',
					'host' => 'A host for scripts check',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #22 Event IPMI without confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event IPMI without confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'IPMI',
						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P {MANUALINPUT} -L user sensor',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
								'one digit, one special character and minimum eight in length',
						'Default input string' => 'Ex@mple7',
						'Input validation rule' => '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
						'Enable confirmation' => false
					],
					'manualinput' => '?',
					'prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
							'one digit, one special character and minimum eight in length',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #23 Event IPMI with confirmation message.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Name' => 'Event IPMI with confirmation message',
						'Scope' => 'Manual event action',
						'Type' => 'IPMI',
						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P {MANUALINPUT} -L user sensor',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
								'one digit, one special character and minimum eight in length',
						'Default input string' => 'Ex@mple7',
						'Input validation rule' => '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
						'Enable confirmation' => true,
						'Confirmation text' => 'Are you sure?'
					],
					'manualinput' => '',
					'prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
							'one digit, one special character and minimum eight in length',
					'event' => 'Attention: script execution is needed',
					'error_message' => 'Incorrect value for field "manualinput": input does not match the provided pattern: '.
							'^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$.',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #24 Host url without confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host url without confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Open in a new window' => false,
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter host id',
						'Default input string' => '1',
						'Input validation rule' => '\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 1-99999 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => 'id',
					'prompt' => 'Enter host id',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #25 Host url without confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host url without confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Open in a new window' => false,
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose host id',
						'Input type' => 'Dropdown',
						'Dropdown options' => '10080,10084,10081,',
						'Enable confirmation' => false
					],
					'manualinput' => 'id',
					'prompt' => 'Choose host id',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #26 Event url without confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Event url without confirmation message and with input type - string',
						'Scope' => 'Manual event action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Open in a new window' => false,
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter host id',
						'Default input string' => '1',
						'Input validation rule' => '\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 1-99999 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => 'id',
					'prompt' => 'Enter host id',
					'event' => 'Attention: script execution is needed',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #27 Event url without confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Event url without confirmation message and with input type - dropdown',
						'Scope' => 'Manual event action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Open in a new window' => false,
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose host id',
						'Input type' => 'Dropdown',
						'Dropdown options' => '10080,10084,10081,',
						'Enable confirmation' => false
					],
					'manualinput' => 'id',
					'prompt' => 'Choose host id',
					'event' => 'Attention: script execution is needed',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #28 Host url with confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host url with confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Open in a new window' => false,
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter host id',
						'Default input string' => '1',
						'Input validation rule' => '\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 1-99999 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Confirm selected host?'
					],
					'manualinput' => 'id',
					'prompt' => 'Enter host id',
					'confirmation' => 'Confirm selected host?',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #29 Host url with confirmation message andwith input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host url with confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Open in a new window' => false,
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose host id',
						'Input type' => 'Dropdown',
						'Dropdown options' => '10080,10084,10081,',
						'Enable confirmation' => true,
						'Confirmation text' => 'Confirm selected host?'
					],
					'manualinput' => 'id',
					'prompt' => 'Choose host id',
					'confirmation' => 'Confirm selected host?',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #30 Event url with confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Event url with confirmation message and with input type - string',
						'Scope' => 'Manual event action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Open in a new window' => false,
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter host id',
						'Default input string' => '1',
						'Input validation rule' => '\b([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 1-99999 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Confirm selected host?'
					],
					'manualinput' => 'id',
					'prompt' => 'Enter host id',
					'confirmation' => 'Confirm selected host?',
					'event' => 'Attention: script execution is needed',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #31 Event url with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Event url with confirmation message and with input type - dropdown',
						'Scope' => 'Manual event action',
						'Type' => 'URL',
						'URL' => 'zabbix.php?action=host.edit&hostid={MANUALINPUT}',
						'Open in a new window' => false,
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose host id',
						'Input type' => 'Dropdown',
						'Dropdown options' => '10080,10084,10081,',
						'Enable confirmation' => true,
						'Confirmation text' => 'Confirm selected host?'
					],
					'manualinput' => 'id',
					'prompt' => 'Choose host id',
					'confirmation' => 'Confirm selected host?',
					'event' => 'Attention: script execution is needed',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #32 Host webhook without confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Host webhook without confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b', // regex 1-9 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => '2',
					'prompt' => 'Enter value for parameter A',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #33 Host webhook without confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Host webhook without confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Input type' => 'Dropdown',
						'Dropdown options' => '1,,2,3',
						'Enable confirmation' => false
					],
					'manualinput' => '3',
					'prompt' => 'Enter value for parameter A',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// TODO: uncomment below test cases when ZBX-24042 will be fixed.
			// #Event webhook without confirmation message and with input type - string (default).
//			[
//				[
//					'expected' => TEST_GOOD,
//					'parameters' => [
//						[
//							'action' => USER_ACTION_ADD,
//							'Name' => 'A',
//							'Value' => '{MANUALINPUT}'
//						]
//					],
//					'fields' => [
//						'Name' => 'Event webhook without confirmation message and with input type - string',
//						'Scope' => 'Manual event action',
//						'Type' => 'Webhook',
//						'Script' => 'var params = JSON.parse(value); return params.a;',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'Enter value for parameter A',
//						'Default input string' => '1',
//						'Input validation rule' => '\b[1-9]\b', // regex 1-9 for form validation.
//						'Enable confirmation' => false
//					],
//					'manualinput' => '9',
//					'prompt' => 'Enter value for parameter A',
//					'event' => 'Attention: script execution is needed',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
//			// #Event webhook without confirmation message and with input type - dropdown.
//			[
//				[
//					'expected' => TEST_GOOD,
//					'parameters' => [
//						[
//							'action' => USER_ACTION_ADD,
//							'Name' => 'A',
//							'Value' => '{MANUALINPUT}'
//						]
//					],
//					'fields' => [
//						'Name' => 'Event webhook without confirmation message and with input type - dropdown',
//						'Scope' => 'Manual event action',
//						'Type' => 'Webhook',
//						'Script' => 'var params = JSON.parse(value); return params.a;',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'Enter value for parameter A',
//						'Input type' => 'Dropdown',
//						'Dropdown options' => '1,,2,3',
//						'Enable confirmation' => false
//					],
//					'manualinput' => '3',
//					'prompt' => 'Enter value for parameter A',
//					'event' => 'Attention: script execution is needed',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
			// #34 Host webhook with confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Host webhook with confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b', // regex 1-9 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Parameter A will contain value => {MANUALINPUT}. Proceed?'
					],
					'manualinput' => '9',
					'prompt' => 'Enter value for parameter A',
					'host' => 'A host for scripts check',
					'confirmation' => 'Parameter A will contain value => 9. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #35 Host webhook with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Host webhook with confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Input type' => 'Dropdown',
						'Dropdown options' => ',A,B,C,D',
						'Enable confirmation' => true,
						'Confirmation text' => 'Parameter A will contain value => {MANUALINPUT}. Proceed?'
					],
					'manualinput' => 'B',
					'prompt' => 'Enter value for parameter A',
					'host' => 'A host for scripts check',
					'confirmation' => 'Parameter A will contain value => B. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #36 Event webhook with confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Event webhook with confirmation message and with input type - string',
						'Scope' => 'Manual event action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b', // regex 1-9 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Parameter A will contain value => {MANUALINPUT}. Proceed?'
					],
					'manualinput' => '7',
					'prompt' => 'Enter value for parameter A',
					'event' => 'Attention: script execution is needed',
					'confirmation' => 'Parameter A will contain value => 7. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #37 Event webhook with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'parameters' => [
						[
							'action' => USER_ACTION_ADD,
							'Name' => 'A',
							'Value' => '{MANUALINPUT}'
						]
					],
					'fields' => [
						'Name' => 'Event webhook with confirmation message and with input type - dropdown',
						'Scope' => 'Manual event action',
						'Type' => 'Webhook',
						'Script' => 'var params = JSON.parse(value); return params.a;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter value for parameter A',
						'Input type' => 'Dropdown',
						'Dropdown options' => ',A,B,C,D',
						'Enable confirmation' => true,
						'Confirmation text' => 'Parameter A will contain value => {MANUALINPUT}. Proceed?'
					],
					'manualinput' => 'D',
					'prompt' => 'Enter value for parameter A',
					'event' => 'Attention: script execution is needed',
					'confirmation' => 'Parameter A will contain value => D. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #38 Host script with confirmation message and input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host script with confirmation message and input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'Script',
						'Commands' => 'echo test {MANUALINPUT};',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose supported version',
						'Input type' => 'Dropdown',
						'Dropdown options' => '6.0,6.4,7.0',
						'Enable confirmation' => true,
						'Confirmation text' => 'Confirm {MANUALINPUT} as supported version?'
					],
					'manualinput' => '6.4',
					'prompt' => 'Choose supported version',
					'confirmation' => 'Confirm 6.4 as supported version?',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #39 Host script with confirmation message and with input type - string.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host script with confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'Script',
						'Commands' => 'ping -c {MANUALINPUT} {HOST.HOST};',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter 🚩{HOST.HOST}🚩 ping count',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b',
						'Enable confirmation' => true,
						'Confirmation text' => 'Ping count: {MANUALINPUT}'
					],
					'manualinput' => '2',
					'prompt' => 'Enter 🚩A host for scripts check🚩 ping count',
					'confirmation' => 'Ping count: 2',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #40 Host script without confirmation message and with input type - string.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host script without confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'Script',
						'Commands' => 'ping -c {MANUALINPUT} {HOST.HOST};',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter 🚩{HOST.HOST}🚩 ping count',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b',
						'Enable confirmation' => false
					],
					'manualinput' => '2',
					'prompt' => 'Enter 🚩A host for scripts check🚩 ping count',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #41 Host script without confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host script without confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'Script',
						'Commands' => 'echo test {MANUALINPUT};',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose supported version',
						'Input type' => 'Dropdown',
						'Dropdown options' => '6.0,6.4,7.0',
						'Enable confirmation' => false
					],
					'manualinput' => '7.0',
					'prompt' => 'Choose supported version',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// TODO: uncomment when ZBX-24042 will be fixed.
			// Manual event script without confirmation message and with input type - dropdown.
//			[
//				[
//					'expected' => TEST_GOOD,
//					'fields' => [
//						'Name' => 'Manual event script without confirmation message and with input type - dropdown',
//						'Scope' => 'Manual event action',
//						'Type' => 'Script',
//						'Commands' => 'echo test;',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'Choose supported version',
//						'Input type' => 'Dropdown',
//						'Dropdown options' => '6.0,6.4,7.0',
//						'Enable confirmation' => false
//					],
//					'manualinput' => '7.0',
//					'prompt' => 'Choose supported version',
//					'event' => 'Attention: script execution is needed',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
			// #42 Manual event script without confirmation message and with input type - string.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Manual event script without confirmation message and with input type - string',
						'Scope' => 'Manual event action',
						'Type' => 'Script',
						'Commands' => 'ping -c {MANUALINPUT} {HOST.HOST};',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter 🚩{HOST.HOST}🚩 ping count',
						'Default input string' => '1',
						'Input validation rule' => '\b[1-9]\b',
						'Enable confirmation' => false
					],
					'manualinput' => '2',
					'prompt' => 'Enter 🚩A host for scripts check🚩 ping count',
					'event' => 'Attention: script execution is needed',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #43 Manual event script with confirmation message and with input type - string.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Manual event script with confirmation message and input type - string',
						'Scope' => 'Manual event action',
						'Type' => 'Script',
						'Commands' => 'echo test;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Test version?',
						'Default input string' => 'Zabbix 7.0.0',
						'Input validation rule' => 'Zabbix [0-9]+\.[0-9]\.[0-9]+',
						'Enable confirmation' => true,
						'Confirmation text' => 'Selected version is {MANUALINPUT}, proceed?'
					],
					'manualinput' => 'Zabbix 6.4.11',
					'prompt' => 'Test version?',
					'confirmation' => 'Selected version is Zabbix 6.4.11, proceed?',
					'event' => 'Attention: script execution is needed',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #44 Manual event script with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Manual event script with confirmation message and input type - dropdown',
						'Scope' => 'Manual event action',
						'Type' => 'Script',
						'Commands' => 'echo test;',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose supported version',
						'Input type' => 'Dropdown',
						'Dropdown options' => '6.0,6.4,7.0',
						'Enable confirmation' => true,
						'Confirmation text' => 'Confirm {MANUALINPUT} as supported version?'
					],
					'manualinput' => '7.0',
					'prompt' => 'Choose supported version',
					'confirmation' => 'Confirm 7.0 as supported version?',
					'event' => 'Attention: script execution is needed',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #45 Host SSH without confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host SSH without confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter hostname',
						'Default input string' => 'Aa',
						'Input validation rule' => '[A-Za-z]', // all letters (uppercase and lowercase).
						'Enable confirmation' => false
					],
					'manualinput' => 'TestHost',
					'prompt' => 'Enter hostname',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #46 Host SSH without confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host SSH without confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose hostname',
						'Input type' => 'Dropdown',
						'Dropdown options' => 'AnyHost,,TestHost,TestZabbix',
						'Enable confirmation' => false
					],
					'manualinput' => 'TestHost',
					'prompt' => 'Choose hostname',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// TODO: uncomment below test cases when ZBX-24042 will be fixed.
			// Event SSH without confirmation message and with input type - string (default).
//			[
//				[
//					'expected' => TEST_GOOD,
//					'fields' => [
//						'Name' => 'Event SSH without confirmation message and with input type - string',
//						'Scope' => 'Manual event action',
//						'Type' => 'SSH',
//						'Username' => 'zabbix',
//						'Commands' => 'ssh zabbix@{MANUALINPUT}',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'Enter hostname',
//						'Default input string' => 'Aa',
//						'Input validation rule' => '[A-Za-z]', // all letters (uppercase and lowercase).
//						'Enable confirmation' => false
//					],
//					'manualinput' => 'TestHost',
//					'prompt' => 'Enter hostname',
//					'event' => 'Attention: script execution is needed',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
//			// Event SSH without confirmation message and with input type - dropdown.
//			[
//				[
//					'expected' => TEST_GOOD,
//					'fields' => [
//						'Name' => 'Event SSH without confirmation message and with input type - dropdown',
//						'Scope' => 'Manual event action',
//						'Type' => 'SSH',
//						'Username' => 'zabbix',
//						'Commands' => 'ssh zabbix@{MANUALINPUT}',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'Choose hostname',
//						'Input type' => 'Dropdown',
//						'Dropdown options' => 'AnyHost,,TestHost,TestZabbix',
//						'Enable confirmation' => false
//					],
//					'manualinput' => 'TestZabbix',
//					'prompt' => 'Choose hostname',
//					'event' => 'Attention: script execution is needed',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
			// #47 Host SSH with confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host SSH with confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter hostname',
						'Default input string' => 'Aa',
						'Input validation rule' => '[A-Za-z]', // all letters (uppercase and lowercase).
						'Enable confirmation' => true,
						'Confirmation text' => 'Hostname is {MANUALINPUT}'
					],
					'manualinput' => 'TestHost',
					'prompt' => 'Enter hostname',
					'host' => 'A host for scripts check',
					'confirmation' => 'Hostname is TestHost',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #48 Host SSH with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host SSH with confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose hostname',
						'Input type' => 'Dropdown',
						'Dropdown options' => 'AnyHost,,TestHost,TestZabbix',
						'Enable confirmation' => true,
						'Confirmation text' => 'Hostname is {MANUALINPUT}'
					],
					'manualinput' => 'AnyHost',
					'prompt' => 'Choose hostname',
					'host' => 'A host for scripts check',
					'confirmation' => 'Hostname is AnyHost',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #49 Event SSH with confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Event SSH with confirmation message and with input type - string',
						'Scope' => 'Manual event action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter hostname',
						'Default input string' => 'Aa',
						'Input validation rule' => '[A-Za-z]', // all letters (uppercase and lowercase).
						'Enable confirmation' => true,
						'Confirmation text' => 'Hostname is {MANUALINPUT}'
					],
					'manualinput' => 'TestHost',
					'prompt' => 'Enter hostname',
					'event' => 'Attention: script execution is needed',
					'confirmation' => 'Hostname is TestHost',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #50 Event SSH with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Event SSH with confirmation message and with input type - dropdown',
						'Scope' => 'Manual event action',
						'Type' => 'SSH',
						'Username' => 'zabbix',
						'Commands' => 'ssh zabbix@{MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose hostname',
						'Input type' => 'Dropdown',
						'Dropdown options' => 'AnyHost,,TestHost,TestZabbix',
						'Enable confirmation' => true,
						'Confirmation text' => 'Hostname is {MANUALINPUT}'
					],
					'manualinput' => 'TestZabbix',
					'prompt' => 'Choose hostname',
					'event' => 'Attention: script execution is needed',
					'confirmation' => 'Hostname is TestZabbix',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #51 Host Telnet without confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host Telnet without confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter port',
						'Default input string' => '22',
						'Input validation rule' => '\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 10-99999 for form validation.
						'Enable confirmation' => false
					],
					'manualinput' => '777',
					'prompt' => 'Enter port',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #52 Host Telnet without confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host Telnet without confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose port',
						'Input type' => 'Dropdown',
						'Dropdown options' => '22,23,999,10050',
						'Enable confirmation' => false
					],
					'manualinput' => '10050',
					'prompt' => 'Choose port',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// TODO: uncomment below test cases when ZBX-24042 will be fixed.
			// Event Telnet without confirmation message and with input type - string (default).
//			[
//				[
//					'expected' => TEST_GOOD,
//					'fields' => [
//						'Name' => 'Event Telnet without confirmation message and with input type - string',
//						'Scope' => 'Manual event action',
//						'Type' => 'Telnet',
//						'Username' => 'zabbix',
//						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'Enter port',
//						'Default input string' => '22',
//						'Input validation rule' => '\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
//								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 10-99999 for form validation.
//						'Enable confirmation' => false
//					],
//					'manualinput' => '23',
//					'prompt' => 'Enter port',
//					'event' => 'Attention: script execution is needed',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
//			// Event Telnet without confirmation message and with input type - dropdown.
//			[
//				[
//					'expected' => TEST_GOOD,
//					'fields' => [
//						'Name' => 'Event Telnet without confirmation message and with input type - dropdown',
//						'Scope' => 'Manual event action',
//						'Type' => 'Telnet',
//						'Username' => 'zabbix',
//						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'Choose port',
//						'Input type' => 'Dropdown',
//						'Dropdown options' => '22,23,999,10050',
//						'Enable confirmation' => false
//					],
//					'manualinput' => '999',
//					'prompt' => 'Choose port',
//					'event' => 'Attention: script execution is needed',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
			// #53 Host Telnet with confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host Telnet with confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter port',
						'Default input string' => '22',
						'Input validation rule' => '\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 10-99999 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Selected port:{MANUALINPUT}. Proceed?'
					],
					'manualinput' => '9000',
					'prompt' => 'Enter port',
					'host' => 'A host for scripts check',
					'confirmation' => 'Selected port:9000. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #54 Host Telnet with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host Telnet with confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose port',
						'Input type' => 'Dropdown',
						'Dropdown options' => '22,23,999,10050',
						'Enable confirmation' => true,
						'Confirmation text' => 'Selected port:{MANUALINPUT}. Proceed?'
					],
					'manualinput' => '23',
					'prompt' => 'Choose port',
					'host' => 'A host for scripts check',
					'confirmation' => 'Selected port:23. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #55 Event Telnet with confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Event Telnet with confirmation message and with input type - string',
						'Scope' => 'Manual event action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Enter port',
						'Default input string' => '22',
						'Input validation rule' => '\b([1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]'.
								'|[1-9][0-9][0-9][0-9][0-9])\b', // regex 10-99999 for form validation.
						'Enable confirmation' => true,
						'Confirmation text' => 'Selected port:{MANUALINPUT}. Proceed?'
					],
					'manualinput' => '10051',
					'prompt' => 'Enter port',
					'event' => 'Attention: script execution is needed',
					'confirmation' => 'Selected port:10051. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #56 Event Telnet with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Event Telnet with confirmation message and with input type - dropdown',
						'Scope' => 'Manual event action',
						'Type' => 'Telnet',
						'Username' => 'zabbix',
						'Commands' => 'telnet 127.0.0.1 {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose port',
						'Input type' => 'Dropdown',
						'Dropdown options' => '22,23,999,10050',
						'Enable confirmation' => true,
						'Confirmation text' => 'Selected port:{MANUALINPUT}. Proceed?'
					],
					'manualinput' => '10050',
					'prompt' => 'Choose port',
					'event' => 'Attention: script execution is needed',
					'confirmation' => 'Selected port:10050. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #57 Host IPMI without confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host IPMI without confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'IPMI',
						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P {MANUALINPUT} -L user sensor',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
								'one digit, one special character and minimum eight in length',
						'Default input string' => 'Ex@mple7',
						'Input validation rule' => '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
						'Enable confirmation' => false
					],
					'manualinput' => 'gNuSm@s2',
					'prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
							'one digit, one special character and minimum eight in length',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #58 Host IPMI without confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host IPMI without confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'IPMI',
						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P test -L sensor get {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose particular sensor',
						'Input type' => 'Dropdown',
						'Dropdown options' => 'IPMI Watchdog,CPU Therm Trip,BB +1.05V PCH,',
						'Enable confirmation' => false
					],
					'manualinput' => 'IPMI Watchdog',
					'prompt' => 'Choose particular sensor',
					'host' => 'A host for scripts check',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// TODO: uncomment below test cases when ZBX-24042 will be fixed.
			// Event IPMI without confirmation message and with input type - string (default).
//			[
//				[
//					'expected' => TEST_GOOD,
//					'fields' => [
//						'Name' => 'Event IPMI without confirmation message and with input type - string',
//						'Scope' => 'Manual event action',
//						'Type' => 'IPMI',
//						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P {MANUALINPUT} -L user sensor',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
//								'one digit, one special character and minimum eight in length',
//						'Default input string' => 'Ex@mple7',
//						'Input validation rule' => '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
//						'Enable confirmation' => false
//					],
//					'manualinput' => 'gNuSm@s2',
//					'prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
//							'one digit, one special character and minimum eight in length',
//					'event' => 'Attention: script execution is needed',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
//			// Event IPMI without confirmation message and with input type - dropdown.
//			[
//				[
//					'expected' => TEST_GOOD,
//					'fields' => [
//						'Name' => 'Event IPMI without confirmation message and with input type - dropdown',
//						'Scope' => 'Manual event action',
//						'Type' => 'IPMI',
//						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P test -L sensor get {MANUALINPUT}',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'Choose particular sensor',
//						'Input type' => 'Dropdown',
//						'Dropdown options' => 'IPMI Watchdog,CPU Therm Trip,BB +1.05V PCH,',
//						'Enable confirmation' => false
//					],
//					'manualinput' => 'BB +1.05V PCH',
//					'prompt' => 'Choose particular sensor',
//					'event' => 'Attention: script execution is needed',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
			// #59 Host IPMI with confirmation message and with input type - string (default).
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host IPMI with confirmation message and with input type - string',
						'Scope' => 'Manual host action',
						'Type' => 'IPMI',
						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P {MANUALINPUT} -L user sensor',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
								'one digit, one special character and minimum eight in length',
						'Default input string' => 'Ex@mple7',
						'Input validation rule' => '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
						'Enable confirmation' => true,
						'Confirmation text' => 'Are you sure?'
					],
					'manualinput' =>'gNuSm@s2',
					'prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
							'one digit, one special character and minimum eight in length',
					'host' => 'A host for scripts check',
					'confirmation' => 'Are you sure?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// #60 Host IPMI with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Host IPMI with confirmation message and with input type - dropdown',
						'Scope' => 'Manual host action',
						'Type' => 'IPMI',
						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P test -L sensor get {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose particular sensor',
						'Input type' => 'Dropdown',
						'Dropdown options' => 'IPMI Watchdog,CPU Therm Trip,BB +1.05V PCH,',
						'Enable confirmation' => true,
						'Confirmation text' => 'Selected sensor:{MANUALINPUT}. Proceed?'
					],
					'manualinput' => 'BB +1.05V PCH',
					'prompt' => 'Choose particular sensor',
					'host' => 'A host for scripts check',
					'confirmation' => 'Selected sensor:BB +1.05V PCH. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Hosts' => 'zabbix.php?action=host.view',
						'Latest data' => 'zabbix.php?action=latest.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			],
			// TODO: uncomment when ZBX-24042 will be fixed.
			// Event IPMI with confirmation message and with input type - string (default).
//			[
//				[
//					'expected' => TEST_GOOD,
//					'fields' => [
//						'Name' => 'Event IPMI with confirmation message and with input type - string',
//						'Scope' => 'Manual event action',
//						'Type' => 'IPMI',
//						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P {MANUALINPUT} -L user sensor',
//						'Advanced configuration' => true,
//						'Enable user input' => true,
//						'Input prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
//								'one digit, one special character and minimum eight in length',
//						'Default input string' => 'Ex@mple7',
//						'Input validation rule' => '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$',
//						'Enable confirmation' => true,
//						'Confirmation text' => 'Are you sure?'
//					],
//					'manualinput' => 'gNuSm@s2',
//					'prompt' => 'regex will enforce these rules: At least one upper case letter, one lower case letter'.
//							'one digit, one special character and minimum eight in length',
//					'event' => 'Attention: script execution is needed',
//					'confirmation' => 'Are you sure?',
//					'urls' => [
//						'Problems' => 'zabbix.php?action=problem.view',
//						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
//					]
//				]
//			],
			// #61 Event IPMI with confirmation message and with input type - dropdown.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Name' => 'Event IPMI with confirmation message and with input type - dropdown',
						'Scope' => 'Manual event action',
						'Type' => 'IPMI',
						'Command' => 'ipmitool -I lan -H localhost -U zabbix -P test -L sensor get {MANUALINPUT}',
						'Advanced configuration' => true,
						'Enable user input' => true,
						'Input prompt' => 'Choose particular sensor',
						'Input type' => 'Dropdown',
						'Dropdown options' => 'IPMI Watchdog,CPU Therm Trip,BB +1.05V PCH,',
						'Enable confirmation' => true,
						'Confirmation text' => 'Selected sensor:{MANUALINPUT}. Proceed?'
					],
					'manualinput' => 'BB +1.05V PCH',
					'prompt' => 'Choose particular sensor',
					'event' => 'Attention: script execution is needed',
					'confirmation' => 'Selected sensor:BB +1.05V PCH. Proceed?',
					'urls' => [
						'Problems' => 'zabbix.php?action=problem.view',
						'Global view' => 'zabbix.php?action=dashboard.view&dashboardid=1'
					]
				]
			]
		];
	}

	/**
	 * @dataProvider getManualInputData
	 */
	public function testManualActionScripts_ManualUserInput($data) {
		$this->page->login()->open('zabbix.php?action=script.list');
		$this->query('button:Create script')->waitUntilClickable()->one()->click();
		$modal = COverlayDialogElement::find()->one()->waitUntilReady();
		$form = $modal->asForm();

		if (($data['manualinput'] === 'id') && (array_key_exists('Dropdown options', $data['fields']))) {
			$data['fields']['Dropdown options'] = $data['fields']['Dropdown options'].self::$hostid;
		}

		if (array_key_exists('parameters', $data)) {
			$modal->query('id:parameters-table')->asMultifieldTable()->one()->fill($data['parameters']);
		}

		$form->fill($data['fields'])->submit();
		$this->assertMessage(TEST_GOOD, 'Script added');

		foreach ($data['urls'] as $content => $url) {
			$this->page->open($url)->waitUntilReady();
			$this->page->assertHeader($content);
			$scope = (array_key_exists('host', $data)) ? 'host' : 'event';

			if ($content === 'Latest data') {
				$this->query('link', $data[$scope])->one()->click();
				$this->page->waitUntilReady();
				$table = $this->query('xpath://table[@class="list-table fixed"]')->asTable()->one();
			}
			elseif ($content === 'Global view') {
				$table = CDashboardElement::find()->one()->getWidget('Current problems');
			}
			else {
				$table = $this->query('class:list-table')->asTable()->one();
			}

			$table->query('link', $data[$scope])->one()->click();
			$popup = CPopupMenuElement::find()->waitUntilVisible()->one();
			$popup->fill($data['fields']['Name']);
			$manualinput_dialog = COverlayDialogElement::find()->waitUntilReady()->one();
			$this->assertEquals('Manual input', $manualinput_dialog->getTitle());
			$this->assertEquals($data['prompt'], $manualinput_dialog->query('class:wordbreak')->one()->getText());

			$manualinput = ($data['manualinput'] === 'id') ? self::$hostid : $data['manualinput'];
			$input_type = (array_key_exists('Input type', $data['fields']))
				? $manualinput_dialog->query('name:manualinput')->asDropdown()->one()->select($manualinput)
				: $manualinput_dialog->query('id:manualinput')->one()->fill($manualinput);

			$action = ($data['fields']['Enable confirmation'] === true) ? 'Continue' : 'Execute';

			// Check if buttons present and clickable.
			$this->assertEquals(['Cancel', $action], $manualinput_dialog->getFooter()->query('button')->all()
					->filter(CElementFilter::CLICKABLE)->asText()
			);
			$manualinput_dialog->getFooter()->query('button', $action)->one()->click();

			if ($data['expected'] === TEST_BAD) {
				$this->assertMessage(TEST_BAD, 'Invalid input', $data['error_message']);
				$manualinput_dialog->close();
			}
			else {
				if (array_key_exists('confirmation', $data)) {
					$confirmation_message = $this->query('class:confirmation-msg')->waitUntilVisible()->one();
					$confirmation_dialog = COverlayDialogElement::find()->all()->last()->waitUntilReady();
					$title = ($data['fields']['Type'] === 'URL') ? 'URL opening confirmation' : 'Execution confirmation';
					$this->assertEquals($title, $confirmation_dialog->getTitle());
					$this->assertEquals($data['confirmation'], $confirmation_message->getText());
					$action = ($data['fields']['Type'] === 'URL') ? 'Open URL' : 'Execute';

					// Check that confirmation popup buttons present and clickable.
					$this->assertEquals(['Cancel', $action], $confirmation_dialog->getFooter()->query('button')->all()
							->filter(CElementFilter::CLICKABLE)->asText()
					);
					$confirmation_dialog->getFooter()->query('button', $action)->one()->click();
				}

				if ($data['fields']['Type'] === 'URL') {
					COverlayDialogElement::ensureNotPresent();
					$host_name = (array_key_exists('host', $data)) ? $data['host'] : 'A host for scripts check';
					$this->assertEquals($host_name, $this->query('id:host')->one()->getValue());
				}
				else {
					$this->query('button:Ok')->waitUntilVisible()->one();
					$output_dialog = COverlayDialogElement::find()->waitUntilReady()->one();
					$this->assertEquals($data['fields']['Name'], $output_dialog->getTitle());

					// Check that Zabbix server is down and return error message.
					$error = "Connection to Zabbix server \"localhost:10051\" refused. Possible reasons:\n".
						"1. Incorrect \"NodeAddress\" or \"ListenPort\" in the \"zabbix_server.conf\" or server IP/DNS override in the \"zabbix.conf.php\";\n".
						"2. Security environment (for example, SELinux) is blocking the connection;\n".
						"3. Zabbix server daemon not running;\n".
						"4. Firewall is blocking TCP connection.\n".
						"Connection refused";
					$this->assertMessage(TEST_BAD, 'Cannot execute script.', $error);
					$this->assertEquals(['Ok'], $output_dialog->getFooter()->query('button')->all()
							->filter(CElementFilter::CLICKABLE)->asText()
					);
					$output_dialog->close();
				}
			}
		}
	}
}
