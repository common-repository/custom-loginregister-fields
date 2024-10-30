=== Custom Login/Register Fields ===
Contributors: ketu762
Tags: custom fields, login, registration, user profile, admin
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom Login/Register Fields allows you to add custom fields to the WordPress registration forms with an easy drag-and-drop interface.

== Description ==

Custom Login/Register Fields plugin allows you to add custom fields to the WordPress registration forms. You can easily add text, email, number, tel, and textarea fields with custom validation rules from the WordPress admin interface. The custom fields can also be managed via a drag-and-drop interface.

= Features =
* Add custom fields to the registration forms.
* Support for text, email, number, tel, and textarea fields.
* Custom validation rules for each field.
* Drag-and-drop interface for managing fields.
* Display custom fields in user profiles.
* Save custom field data in user meta.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/custom-login-register-fields` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to 'Custom Fields' in the WordPress admin menu to start adding custom fields.

== Frequently Asked Questions ==

= How do I add a new custom field? =

Go to the 'Custom Fields' menu in the WordPress admin. Enter the field label, select the field type, and optionally add validation rules. Click 'Add Field' to save the field.

= How can I apply custom validation rules? =

When adding a new field, you can specify validation rules in the 'Validation' input. For example, to make a field required and ensure it matches a pattern, you can use `required|pattern=[0-9]{10}`.

= How can I reorder the custom fields? =

You can reorder fields by dragging and dropping them in the 'Custom Fields' list.

= How do I delete a custom field? =

Click the 'Delete' link next to the field you want to remove in the 'Custom Fields' list.

== Changelog ==

= 1.0 =
* Initial release.

= 1.0.1 =
* This update includes minor security improvements for the plugin.

== Upgrade Notice ==

= 1.0 =
* Initial release.

== License ==

This plugin is licensed under the GPLv2 or later. See the [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html) for more details.
