<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Ogmo_Install {


	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
	}


	public static function install() {
		self::create_capabilities();

		self::update_ogmo_version();
	}

	public static function create_capabilities() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$wp_roles->add_cap( 'shop_manager', Ogmo::CAPABILITY );
		$wp_roles->add_cap( 'administrator', Ogmo::CAPABILITY );
	}


	public static function remove_capabilities() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$wp_roles->remove_cap( 'shop_manager', Ogmo::CAPABILITY );
		$wp_roles->remove_cap( 'administrator', Ogmo::CAPABILITY );
	}



	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'ogmo_version' ) !== get_ogmo()->version ) {
			self::install();
		}
	}


	private static function update_ogmo_version() {
		delete_option( 'ogmo_version' );
		add_option( 'ogmo_version', get_ogmo()->version );
	}
}

Ogmo_Install::init();