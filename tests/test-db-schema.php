<?php
/**
 * Test case for database schema and helpers.
 *
 * @package KI_Kraft
 */

/**
 * Test database schema functionality.
 */
class Test_Kraft_AI_Chat_DB extends WP_UnitTestCase {

	/**
	 * Test schema version functions.
	 */
	public function test_schema_version_functions() {
		// Set schema version
		kraft_ai_chat_set_schema_version( 1 );
		
		// Get schema version
		$version = kraft_ai_chat_get_schema_version();
		
		$this->assertEquals( 1, $version );
	}

	/**
	 * Test sessions table exists.
	 */
	public function test_sessions_table_exists() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'kraft_ai_chat_sessions';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		$result = $wpdb->get_var( $query );
		
		$this->assertEquals( $table_name, $result );
	}

	/**
	 * Test messages table exists.
	 */
	public function test_messages_table_exists() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'kraft_ai_chat_messages';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		$result = $wpdb->get_var( $query );
		
		$this->assertEquals( $table_name, $result );
	}

	/**
	 * Test sessions table structure.
	 */
	public function test_sessions_table_structure() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'kraft_ai_chat_sessions';
		$columns = $wpdb->get_results( "DESCRIBE $table_name" );
		
		$column_names = wp_list_pluck( $columns, 'Field' );
		
		$this->assertContains( 'id', $column_names );
		$this->assertContains( 'session_id', $column_names );
		$this->assertContains( 'user_id', $column_names );
		$this->assertContains( 'context', $column_names );
		$this->assertContains( 'created_at', $column_names );
	}

	/**
	 * Test messages table structure.
	 */
	public function test_messages_table_structure() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'kraft_ai_chat_messages';
		$columns = $wpdb->get_results( "DESCRIBE $table_name" );
		
		$column_names = wp_list_pluck( $columns, 'Field' );
		
		$this->assertContains( 'id', $column_names );
		$this->assertContains( 'session_id', $column_names );
		$this->assertContains( 'sender', $column_names );
		$this->assertContains( 'content', $column_names );
		$this->assertContains( 'sources', $column_names );
		$this->assertContains( 'client_msg_id', $column_names );
		$this->assertContains( 'reply_to_client_msg_id', $column_names );
		$this->assertContains( 'created_at', $column_names );
	}

	/**
	 * Test session_id has unique constraint.
	 */
	public function test_session_id_unique_constraint() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'kraft_ai_chat_sessions';
		$indexes = $wpdb->get_results( "SHOW INDEX FROM $table_name WHERE Key_name = 'session_id'" );
		
		$this->assertNotEmpty( $indexes );
		$this->assertEquals( 1, $indexes[0]->Non_unique );
	}

	/**
	 * Test table helper functions.
	 */
	public function test_table_helper_functions() {
		global $wpdb;
		
		$sessions_table = kraft_ai_chat_sessions_table();
		$messages_table = kraft_ai_chat_messages_table();
		
		$this->assertEquals( $wpdb->prefix . 'kraft_ai_chat_sessions', $sessions_table );
		$this->assertEquals( $wpdb->prefix . 'kraft_ai_chat_messages', $messages_table );
	}

	/**
	 * Test inserting a session record.
	 */
	public function test_insert_session_record() {
		global $wpdb;
		
		$session_id = 'sess_test_' . bin2hex( random_bytes( 8 ) );
		$user_id = $this->factory->user->create();
		
		$result = $wpdb->insert(
			$wpdb->prefix . 'kraft_ai_chat_sessions',
			array(
				'session_id' => $session_id,
				'user_id'    => $user_id,
				'context'    => 'member',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%s' )
		);
		
		$this->assertEquals( 1, $result );
		
		// Verify record was inserted
		$record = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kraft_ai_chat_sessions WHERE session_id = %s",
				$session_id
			)
		);
		
		$this->assertNotNull( $record );
		$this->assertEquals( $session_id, $record->session_id );
		$this->assertEquals( $user_id, $record->user_id );
		$this->assertEquals( 'member', $record->context );
	}

	/**
	 * Test inserting a message record.
	 */
	public function test_insert_message_record() {
		global $wpdb;
		
		$session_id = 'sess_test_' . bin2hex( random_bytes( 8 ) );
		$user_id = $this->factory->user->create();
		
		// Create session first
		$wpdb->insert(
			$wpdb->prefix . 'kraft_ai_chat_sessions',
			array(
				'session_id' => $session_id,
				'user_id'    => $user_id,
				'context'    => 'member',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%s' )
		);
		
		// Insert message
		$message_content = 'Test message';
		$client_msg_id = 'msg_' . bin2hex( random_bytes( 8 ) );
		
		$result = $wpdb->insert(
			$wpdb->prefix . 'kraft_ai_chat_messages',
			array(
				'session_id'    => $session_id,
				'sender'        => 'user',
				'content'       => $message_content,
				'client_msg_id' => $client_msg_id,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);
		
		$this->assertEquals( 1, $result );
		
		// Verify record was inserted
		$record = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kraft_ai_chat_messages WHERE client_msg_id = %s",
				$client_msg_id
			)
		);
		
		$this->assertNotNull( $record );
		$this->assertEquals( $session_id, $record->session_id );
		$this->assertEquals( 'user', $record->sender );
		$this->assertEquals( $message_content, $record->content );
	}
}
