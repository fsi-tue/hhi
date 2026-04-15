<?php
const I18N_DEFAULT_LOCALE = "de";

$i18nLocale = I18N_DEFAULT_LOCALE;
$i18nMessages = [];

function i18n_set_locale(string $locale = I18N_DEFAULT_LOCALE): void {
    global $i18nLocale, $i18nMessages;

    $locale = preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/', $locale) ? $locale : I18N_DEFAULT_LOCALE;
    $i18nLocale = $locale;

    $defaultMessages = i18n_load_file(I18N_DEFAULT_LOCALE);
    $localeMessages = $locale === I18N_DEFAULT_LOCALE ? [] : i18n_load_file($locale);
    $i18nMessages = array_replace($defaultMessages, $localeMessages);
}

function i18n_load_file(string $locale): array {
    $path = dirname(__DIR__) . "/i18n/{$locale}.json";
    if( ! is_readable($path)) {
        return [];
    }

    $messages = json_decode(file_get_contents($path), true);
    return is_array($messages) ? $messages : [];
}

function i18n(string $id, array $params = []): string {
    global $i18nMessages;

    $message = $i18nMessages[$id] ?? $id;
    if($params === []) {
        return $message;
    }

    $replace = [];
    foreach($params as $key => $value) {
        $replace["{" . $key . "}"] = (string) $value;
    }
    return strtr($message, $replace);
}

?>
