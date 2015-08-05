<?php
/**
 * Implements hook_form_system_theme_settings_alter() function.
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 * @param $form_state
 *   A keyed array containing the current state of the form.
 */
function islandora_camp_form_system_theme_settings_alter(&$form, $form_state, $form_id = NULL) {
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }
  $form['islandora_camp_settings_custom'] = array(
    '#type' => 'fieldset',
    '#title' => t('Custom Islandora camp Theme Settings'),
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
  );
  $form['islandora_camp_settings_custom']['slideshow_bg_datastream'] = array(
    '#type' => 'textfield',
    '#title' => t('Slideshow Background Datastream'),
    '#default_value' => (theme_get_setting('slideshow_bg_datastream', 'islandora_camp') ? theme_get_setting('slideshow_bg_datastream', 'islandora_camp') : "TN" ),
    '#description'   => t("The datastream to as an objects image representation in a slideshow (EX: OBJ, MEDIUM, TN), defaults to TN"),
  );
  $form['islandora_camp_settings_custom']['advanced_collection_view'] = array(
    '#type' => 'textfield',
    '#title' => t('Advanced collection view PIDS'),
    '#default_value' => theme_get_setting('advanced_collection_view'),
    '#description'   => t("A comma seperated list of PIDS to be presented utilizing a more Advanced collection view. Add 'all' to apply to all collections"),
  );
  $form['islandora_camp_settings_custom']['show_collection_metadata'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show collection level metadata'),
    '#default_value' => theme_get_setting('show_collection_metadata'),
    '#description'   => t("Select to show collection level metadata on collection pages"),
  );
  $form['islandora_camp_settings_custom']['advanced_collection_search_view'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show search block on collection pages'),
    '#default_value' => theme_get_setting('advanced_collection_search_view'),
    '#description'   => t("Select to show islandora simple search on all collection landing page."),
  );
}
