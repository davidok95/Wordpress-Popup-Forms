<?php
/*
Plugin Name: Davidok95 callback
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists("Davidok95Callback"))
{
	class Davidok95Callback
	{
		public static $pluginUrl;
		private static $theme;

		public static function init()
		{
			add_action("wp_footer", array(__CLASS__, "show"), 10);
			self::$pluginUrl = plugin_dir_url(__FILE__);
			add_action( 'wp_enqueue_scripts', array(__CLASS__, "include_css"));
			add_action( 'wp_enqueue_scripts', array(__CLASS__, "include_js"));
			add_action( 'wp_ajax_davidok95_callback', array(__CLASS__, "ajax_handler"));
			add_action('admin_menu', array(__CLASS__, "add_options_page"));
			add_action('admin_init', array(__CLASS__, 'settings_init'));

			self::$theme = get_option('davidok95callback_theme', 'base');
		}

		public static function include_js()
		{
			$nonce = wp_create_nonce( 'davidok95Callback' );
			$add_text_to_message = (get_option('davidok95callback_add-button-text-to-message', '') != '' ? "Y" : "N");
			
			
			$scripts = array(
				"davidok95-callback" => "public/js/davidok95-callback.js",
				"davidok95-callback-jquery-ui" => "public/jquery-ui-1.12.1." . self::$theme . "/jquery-ui.min.js",
			);
			foreach ($scripts as $key => $script)
			{
				wp_register_script($key, self::$pluginUrl . $script, array("jquery"), '1.0', true );
				wp_enqueue_script($key);
			}
			
			wp_localize_script( 'davidok95-callback', 'my_ajax_obj', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => $nonce,
				'add_button_text_to_message' => $add_text_to_message,
			) ); 
		}

		public static function include_css()
		{
			$styles = array(
				"davidok95-callback-jquery-ui" => "/public/jquery-ui-1.12.1." . self::$theme . "/jquery-ui.css",
				"davidok95-callback" => "/public/css/davidok95-callback.css",
			);
			foreach ($styles as $key => $style)
			{
				wp_register_style($key, self::$pluginUrl . $style, false, '2.0', "all");
				wp_enqueue_style($key);
			}
		}

		public static function ajax_handler()
		{
			check_ajax_referer('davidok95Callback');

			$to = get_option("davidok95callback_email");
			$to = mb_split(',', $to);
			foreach ($to as $key => $val)
				$to[$key] = trim($val);

			$blogName = get_option("blogname");
			$name = sanitize_text_field($_POST["NAME"]);
			$phone = sanitize_text_field($_POST["PHONE"]);
			$buttonText = sanitize_text_field($_POST["ADD_BUTTON_TEXT"]);

			$headers = array();
			$subject = "Сайт {$blogName} - форма обратного звонка";
			$message = "Имя: {$name}\nТелефон: {$phone}";

			$addButtonText = get_option('davidok95callback_add-button-text-to-message');
			if ($addButtonText == "Y")
				$message .= "\nТекст кнопки: {$buttonText}";

			$response = array(
				"result" => "success",
				"subject" => $subject,
				"message" => $message,
			);

			if (wp_mail($to, $subject, $message, $headers))
			{
				$response["result"] = "success";
				wp_send_json($response);
			}

			$response["result"] = "error";
			wp_send_json($response);
		}

		public static function settings_init()
		{
			register_setting('davidok95callback', 'davidok95callback_email');
			register_setting('davidok95callback', 'davidok95callback_add-button-text-to-message');
			register_setting('davidok95callback', 'davidok95callback_theme');

			add_settings_section(
				'davidok95callback_settings_section',
				'Настроки',
				'wporg_settings_section_cb',
				'davidok95callback'
			);

			add_settings_field(
				'davidok95callback_email',
				'Email получателя',
				array(__CLASS__, 'email_field'),
				'davidok95callback',
				'davidok95callback_settings_section'
			);

			add_settings_field(
				'davidok95callback_add-button-text-to-message',
				'Добавить текст кнопки в сообщение',
				array(__CLASS__, 'add_button_text_to_message_field'),
				'davidok95callback',
				'davidok95callback_settings_section'
			);

			add_settings_field(
				'davidok95callback_theme',
				'Внешний вид',
				array(__CLASS__, 'theme_field'),
				'davidok95callback',
				'davidok95callback_settings_section'
			);

		}

		function email_field()
		{
			// get the value of the setting we've registered with register_setting()
			$setting = get_option('davidok95callback_email');
			// output the field
			?>
			<input type="text" name="davidok95callback_email" value="<?= isset($setting) ? esc_attr($setting) : ''; ?>">
			<?php
		}

		function theme_field()
		{
			$options = array(
				'base' => 'Base',
				'lightness' => 'Lightness',
			);

			// get the value of the setting we've registered with register_setting()
			$setting = get_option('davidok95callback_theme', 'base');
			?>
			<select name="davidok95callback_theme">
				<?php foreach ($options as $key => $val) {
					$selected = "";
					if ($key == $setting)
						$selected = " selected";
					?>
						<option value="<?= $key ?>"<?= $selected ?>><?= $val ?></option>
				<?php } ?>
			</select>
			<?php
		}

		function add_button_text_to_message_field()
		{
			// get the value of the setting we've registered with register_setting()
			$setting = get_option('davidok95callback_add-button-text-to-message');
			// output the field
			$checked = (mb_strlen($setting) > 0 ? " checked" : "");
			?>
			<input type="checkbox" name="davidok95callback_add-button-text-to-message" value="Y" <?= $checked ?> >
			<?php
		}

		public static function add_options_page()
		{
			add_submenu_page(
				'options-general.php',
				'Обратный звонок',
				'Обратный звонок',
				'manage_options',
				'davidok95callback',
				array(__CLASS__, "options_page")
			);
		}

		public static function options_page()
		{
			?>
			<div class="wrap">
				<h1><?= esc_html(get_admin_page_title()); ?></h1>
				<form action="options.php" method="post">
					<?php
					// output security fields for the registered setting "wporg_options"
					settings_fields('davidok95callback');
					// output setting sections and their fields
					// (sections are registered for "wporg", each field is registered to a specific section)
					do_settings_sections('davidok95callback');
					// output save settings button
					submit_button('Сохранить настройки');
					?>
				</form>
			</div>
			<?php
		}

		public static function show()
		{
			?>
			<div class="davidok95callback">
				<div id="davidok95-callback" class="davidok95callback davidok95-callback ui-helper-hidden" title="Заказать обратный звонок">
					<form action="/" class="davidok95-callback__form">
						<input type="hidden" name="ADD_BUTTON_TEXT" type="text">
						<div class="davidok95-callback__input-container">
							<label class="davidok95-callback__label">Ваше имя <span>*</span></label><br />
							<input class="davidok95-callback__input davidok95-callback__input-name ui-spinner ui-corner-all" name="NAME" type="text">
						</div>
						<div class="davidok95-callback__input-container">
							<label class="davidok95-callback__label">Ваш телефон <span>*</span></label><br />
							<input class="davidok95-callback__input davidok95-callback__input-phone ui-spinner ui-corner-all" name="PHONE" type="text">
						</div>
					</form>
				</div>
				<div id="davidok95-callback-result" class="davidok95-callback__result ui-helper-hidden" title="Спасибо, Ваш запрос принят.">
					<p>Скоро наш менеджер перезвонит вам по указанному вами телефону</p>
				</div>
				<div class="ui-widget-overlay ui-helper-hidden"></div>
			</div>
			<?php
		}
	}
	Davidok95Callback::init();
}
