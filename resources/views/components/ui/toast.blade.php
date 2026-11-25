@props(['messages' => null])

@php
    $toastMessages = $messages ?? [];

    if ($messages === null) {
        $hasValidationErrors = isset($errors) && $errors->any();
        $hasErrorFlash = session()->has('error');
        $welcome = session('welcome_toast');

        if (
            is_array($welcome)
            && ($welcome['type'] ?? null) === 'success'
            && is_string($welcome['title'] ?? null)
            && is_string($welcome['message'] ?? null)
        ) {
            $toastMessages[] = [
                'type' => 'success',
                'title' => mb_substr(trim($welcome['title']), 0, 80),
                'message' => mb_substr(trim($welcome['message']), 0, 240),
                'duration' => (int) ($welcome['duration'] ?? 5000),
            ];
        }

        foreach (['success', 'status', 'warning', 'info', 'error'] as $key) {
            if ($hasValidationErrors && in_array($key, ['success', 'status'], true)) {
                continue;
            }
            if ($hasErrorFlash && in_array($key, ['success', 'status'], true)) {
                continue;
            }

            $message = session($key);
            if (! is_string($message) || trim($message) === '') {
                continue;
            }

            $type = $key === 'status' ? 'success' : $key;
            if (preg_match('/(?:SQLSTATE|stack trace|exception|password\s*[:=]|token\s*[:=]|bearer\s+)/i', $message)) {
                $message = $type === 'error'
                    ? 'Une erreur est survenue. Réessayez ou contactez le support.'
                    : 'Une opération nécessite votre attention. Réessayez ou contactez le support.';
            }

            $toastMessages[] = [
                'type' => $type,
                'title' => match ($type) {
                    'success' => 'Succès',
                    'error' => 'Une erreur est survenue',
                    'warning' => 'À vérifier',
                    default => 'Information',
                },
                'message' => mb_substr($message, 0, 240),
            ];
        }

        if ($hasValidationErrors) {
            $toastMessages[] = [
                'type' => 'warning',
                'title' => 'Vérifiez votre saisie',
                'message' => 'Certains champs nécessitent une correction. Les détails restent affichés dans le formulaire.',
            ];
        }
    }

    $toastMessages = array_slice($toastMessages, 0, 3);
    $toastPayload = base64_encode(json_encode($toastMessages, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR));
@endphp

<div id="global-toast-root"
     class="ui-toast-root"
     data-toast-payload="{{ $toastPayload }}"
     aria-label="Messages de la plateforme"></div>
