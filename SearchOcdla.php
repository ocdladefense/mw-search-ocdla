<?php

if ( !defined( 'MEDIAWIKI' ) )
	die();

/**
 * General extension information.
 */
$wgExtensionCredits['specialpage'][] = array(
	'path'           				=> __FILE__,
	'name'           				=> 'SearchOcdla',
	'version'        				=> '0.0.0.1',
	'author'         				=> 'José Bernal',
	// 'descriptionmsg' 		=> 'wikilogocdla-desc',
	// 'url'            		=> 'http://www.mediawiki.org/wiki/Extension:WikilogOcdla',
);

// $wgExtensionMessagesFiles['WikilogOcdla'] = $dir . 'WikilogOcdla.i18n.php';

$dir = dirname( __FILE__ );




$overrides = array(
	'SearchEngine'          		=> $dir . '/classes/SearchEngine.php'
);

$wgAutoloadLocalClasses = array_merge($wgAutoloadLocalClasses,$overrides);


class SearchOcdlaHooks {


	public static function SetupSearchOcdla(){
		global $wgHooks, $wgResourceModules;
		
		$wgHooks['SpecialSearchCreateLink'][] = 'SearchOcdlaHooks::onSpecialSearchCreateLink';
		$wgHooks['BeforePageDisplay'][] = 'SearchOcdlaHooks::onBeforePageDisplay';
		

		$wgResourceModules['search.booksonline.js'] = array(
			'scripts' => array(
				'js/search.controller.js'
			),
			'dependencies' => array(
				// In this example, awesome.js needs the jQuery UI dialog stuff
				'clickpdx.framework.js',
			),
			'position' => 'bottom',
			'remoteBasePath' => 'extensions/SearchOcdla',
			'localBasePath' => 'extensions/SearchOcdla'
		);
	}
	
	public static function onBeforePageDisplay(OutputPage &$out, Skin &$skin ) {
		if(in_array(strtolower($out->getPageTitle()),array('search results','search'))) {
			$out->addModules('search.booksonline.js');
		}
		
		return true;
	}

	public static function onSpecialSearchCreateLink($t, &$params)
	{
		// array() // get title variants
		if($t->isKnown())
		{
			return true;
		}
		
		// print "<pre>".print_r($t,true)."</pre>";exit;
		
		$dbr = wfGetDB( DB_SLAVE );
		$page_id = $dbr->selectField(
			'page_ocdlasearch',
			'page_id',
			array(
				'page_title' => array($t->mDbkeyform),
			)
		);
		
		if($page_id)
		{
			$messageName = 'searchmenu-exists';
			$t = Title::newFromID($page_id);
		}
		elseif( $t->userCan( 'create' ) )
		{
			$messageName = 'searchmenu-new';
		}
		else
		{
			$messageName = 'searchmenu-new-nocreate';
		}
		
		$params = array( $messageName, wfEscapeWikiText( $t->getPrefixedText() ) );
		
		
		// print "<h1>Page id: {$page_id}</h1>.";exit;
		/*
					$this->total_hits = $resultSet[ 'total_found' ];
			foreach ( $resultSet['matches'] as $id => $docinfo ) {
				$res = $this->db->select(
					'page',
					array( 'page_id', 'page_title', 'page_namespace' ),
					array( 'page_id' => $id ),
					__METHOD__,
					array()
				);
				if ( $this->db->numRows( $res ) > 0 ) {
					$this->mResultSet[] = $this->db->fetchObject( $res );
				}
			}

		


		$title = Title::newFromText('Speedy xTrial');
		
		if( $t->isKnown() )
		{
			return;
		}
		
		$variants = new TitleVariantCollection($t);
		if($variants->hasKnownVariant())
		{
			$messageName = 'searchmenu-exists';
			$t = $variants->getKnownVariant();
		} elseif( $t->userCan( 'create' ) ) {
			$messageName = 'searchmenu-new';
		} else {
			$messageName = 'searchmenu-new-nocreate';
		}
		
		$params = array( $messageName, wfEscapeWikiText( $t->getPrefixedText() ) );

		// wfRunHooks( '', array( $t, &$params ) );
					*/
		return true;
	}
	
}


class OcdlaSearch
{
	static function getDBData( $dbID, $from, $columns, $where, $sqlOptions, $otherParams ) {
		global $edgDBServerType;
		global $edgDBServer;
		global $edgDBDirectory;
		global $edgDBName;
		global $edgDBUser;
		global $edgDBPass;
		global $edgDBFlags;
		global $edgDBTablePrefix;

		// Get all possible parameters
		$db_type = self::getArrayValue( $edgDBServerType, $dbID );
		$db_server = self::getArrayValue( $edgDBServer, $dbID );
		$db_directory = self::getArrayValue( $edgDBDirectory, $dbID );
		$db_name = self::getArrayValue( $edgDBName, $dbID );
		$db_username = self::getArrayValue( $edgDBUser, $dbID );
		$db_password = self::getArrayValue( $edgDBPass, $dbID );
		$db_flags = self::getArrayValue( $edgDBFlags, $dbID );
		$db_tableprefix = self::getArrayValue( $edgDBTablePrefix, $dbID );

		// MongoDB has entirely different handling from the rest.
		if ( $db_type == 'mongodb' ) {
			if ( $db_name == '' ) {
				return wfMessage( "externaldata-db-incomplete-information" )->text();
			}
			return self::getMongoDBData( $db_server, $db_username, $db_password, $db_name, $from, $columns, $where, $sqlOptions, $otherParams );
		}

		// Validate parameters
		if ( $db_type == '' ) {
			return wfMessage( "externaldata-db-incomplete-information" )->text();
		} elseif ( $db_type == 'sqlite' ) {
			if ( $db_directory == '' || $db_name == '' ) {
				return wfMessage( "externaldata-db-incomplete-information" )->text();
			}
		} else {
			// We don't check the password because it could
			// legitimately be blank or null.
			if ( $db_server == '' || $db_name == '' ||
				$db_username == '' ) {
				return wfMessage( "externaldata-db-incomplete-information" )->text();
			}
		}

		// Additional settings
		if ( $db_type == 'sqlite' ) {
			global $wgSQLiteDataDir;
			$oldDataDir = $wgSQLiteDataDir;
			$wgSQLiteDataDir = $db_directory;
		}
		if ( $db_flags == '' ) {
			$db_flags = DBO_DEFAULT;
		}

		// DatabaseBase::newFromType() was added in MW 1.17 - it was
		// then replaced by DatabaseBase::factory() in MW 1.18
		$factoryFunction = array( 'DatabaseBase', 'factory' );
		//$newFromTypeFunction = array( 'DatabaseBase', 'newFromType' );
		if ( is_callable( $factoryFunction ) ) {
			$db = DatabaseBase::factory( $db_type,
				array(
					'host' => $db_server,
					'user' => $db_username,
					'password' => $db_password,
					// Both 'dbname' and 'dbName' have been
					// used in different versions.
					'dbname' => $db_name,
					'dbName' => $db_name,
					'flags' => $db_flags,
					'tablePrefix' => $db_tableprefix,
				)
			);
		} else { //if ( is_callable( $newFromTypeFunction ) ) {
			$db = DatabaseBase::newFromType( $db_type,
				array(
					'host' => $db_server,
					'user' => $db_username,
					'password' => $db_password,
					'dbname' => $db_name,
					'flags' => $db_flags,
					'tableprefix' => $db_tableprefix,
				)
			);
		}

		if ( $db == null ) {
			return wfMessage( "externaldata-db-unknown-type" )->text();
		}

		if ( ! $db->isOpen() ) {
			return wfMessage( "externaldata-db-could-not-connect" )->text();
		}

		if ( count( $columns ) == 0 ) {
			return wfMessage( "externaldata-db-no-return-values" )->text();
		}

		$rows = self::searchDB( $db, $from, $columns, $where, $sqlOptions );
		$db->close();

		if ( !is_array( $rows ) ) {
			// It's an error message.
			return $rows;
		}

		if ( $db_type == 'sqlite' ) {
			// Reset global variable back to its original value.
			global $wgSQLiteDataDir;
			$wgSQLiteDataDir = $oldDataDir;
		}

		$values = array();
		foreach ( $rows as $row ) {
			foreach ( $columns as $column ) {
				$values[$column][] = $row[$column];
			}
		}

		return $values;
	}
}