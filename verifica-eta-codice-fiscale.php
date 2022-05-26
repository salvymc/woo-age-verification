<?php

/**
 * Verifica Età Codice Fiscale
 *
 * @package       VERIFICAET
 * @author        Salvatore Forino
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Verifica Età Codice Fiscale
 * Plugin URI:    https://goldjuice.it/
 * Description:   Aggiunge un campo Codice Fiscale al carrello e verifica l'età dell'acquirente
 * Version:       1.0.0
 * Author:        Salvatore Forino
 * Author URI:    https://thestalla.it
 * Text Domain:   verifica-eta-codice-fiscale
 * Domain Path:   /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;


/**
 * Aggiunge un nuovo campo nel checkout
 */
add_action('woocommerce_after_order_notes', 'my_custom_checkout_field');

function my_custom_checkout_field($checkout)
{

    echo '<div id="my_custom_checkout_field"><h2>' . __('Codice Fiscale') . '</h2>';

    woocommerce_form_field('verify_cf', array(
        'type'          => 'text',
        'class'         => array('cf_row_age form-row-wide'),
        'label'         => __('Aggiungi un codice fiscale'),
        'placeholder'   => __('Es: TSTTST80A01A089Q'),
    ), $checkout->get_value('verify_cf'));

    echo '</div>';
}

/**
 * Verifica se il campo è vuoto
 * Estrapola l'età dell'acquirente dal codice fiscale e verifica la maggiore età
 */
add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

function my_custom_checkout_field_process()
{
    $rest = substr($_POST['verify_cf'], 6, -8);
    $current_year = date("y");
    $current_year_first = intval(substr(date("Y"), 0, 2));


    if ($rest > $current_year) {
        $y_cf = ($current_year_first - 1) . $rest;
        $age = date("Y") - $y_cf;
    } else {
        $y_cf = $current_year_first . $rest;
        $age = date("Y") - $y_cf;
    }

    // Controllo se il campo è pieno e se il codice fiscale inserito ha 16 caratteri
    if (!$_POST['verify_cf'] || strlen($_POST['verify_cf']) != 16) {
        wc_add_notice(__('Inserisci un codice fiscale valido'), 'error');
    }

    // Controllo la maggiore età
    if (intval($age) < 18) {
        wc_add_notice(__('Vietata la vendita ai minori di 18 anni'), 'error');
    }
}

/**
 * Salva il Codice fiscale nei meta dell'ordine
 */
add_action('woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta');

function my_custom_checkout_field_update_order_meta($order_id)
{
    if (!empty($_POST['verify_cf'])) {
        update_post_meta($order_id, 'Codice Fiscale', sanitize_text_field($_POST['verify_cf']));
    }
}

/**
 * Visualizza il valore nella pagina di gestione ordine
 */
add_action('woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1);

function my_custom_checkout_field_display_admin_order_meta($order)
{
    echo '<p><strong>' . __('Codice Fiscale') . ':</strong> ' . get_post_meta($order->id, 'Codice Fiscale', true) . '</p>';
}
