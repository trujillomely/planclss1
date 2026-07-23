<?php

class FormSubmissionPdfHelper {

    public static function generate($submission, $structure){
        $html = self::buildHtml($submission, $structure);

        $autoloadPath = ROOT_PATH . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('letter', 'portrait');
            $dompdf->render();
            $filename = 'formulario-' . ($submission['submission_number'] ?? 'envio') . '.pdf';
            $dompdf->stream($filename, ['Attachment' => true]);
        } else {
            header('Content-Type: text/html; charset=UTF-8');
            echo $html;
        }
    }

    public static function previewHtml($submission, $structure){
        header('Content-Type: text/html; charset=UTF-8');
        echo self::buildHtml($submission, $structure);
    }

    private static function buildHtml($submission, $structure){
        $formName = htmlspecialchars($submission['form_name'] ?? '');
        $subNumber = htmlspecialchars($submission['submission_number'] ?? '');
        $submittedAt = !empty($submission['submitted_at']) ? date('d/m/Y H:i', strtotime($submission['submitted_at'])) : date('d/m/Y H:i', strtotime($submission['created_at']));
        $status = htmlspecialchars($submission['status'] ?? '');
        $userName = htmlspecialchars(trim(($submission['username'] ?? '') . ' ' . ($submission['lastname'] ?? '')));
        $userEmail = htmlspecialchars($submission['user_email'] ?? '');
        $version = (int)($submission['version_number'] ?? 1);
        $today = date('d/m/Y');

        $statusColors = [
            'Pendiente' => ['#fef3c7', '#92400e'],
            'Enviado'   => ['#dbeafe', '#1e40af'],
            'Revisado'  => ['#d1fae5', '#065f46'],
            'Rechazado' => ['#fee2e2', '#991b1b'],
        ];
        $sc = $statusColors[$status] ?? ['#f3f4f6', '#374151'];

        $valuesMap = [];
        if (!empty($submission['values'])) {
            foreach ($submission['values'] as $v) {
                $fieldId = $v['id_form_field'];
                $valuesMap[$fieldId] = $v;
            }
        }

        $attachmentsMap = [];
        if (!empty($submission['attachments'])) {
            foreach ($submission['attachments'] as $a) {
                $attachmentsMap[$a['id_form_field']] = $a;
            }
        }

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title><?= $formName ?> — Envío <?= $subNumber ?> — Arco Seguros</title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 11pt; color: #1C1C1E; padding: 30px; }
                .header { text-align: center; border-bottom: 3px solid #B4864E; padding-bottom: 14px; margin-bottom: 20px; }
                .header h1 { font-size: 14pt; color: #B4864E; margin-bottom: 4px; letter-spacing: 2px; }
                .header h2 { font-size: 12pt; font-weight: 600; color: #1C1C1E; margin-bottom: 4px; }
                .header p { font-size: 8pt; color: #6b7280; }
                .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9pt; }
                .meta-table td { padding: 6px 10px; border-bottom: 1px solid #f3f4f6; }
                .meta-table td:first-child { font-weight: 700; color: #6b7280; width: 35%; text-transform: uppercase; letter-spacing: 0.03em; font-size: 7.5pt; }
                .meta-table td:last-child { color: #1C1C1E; }
                .status-badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 8pt; font-weight: 700; background: <?= $sc[0] ?>; color: <?= $sc[1] ?>; }
                .section-title { font-size: 10pt; font-weight: 700; color: #B4864E; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin: 18px 0 10px; }
                .section-desc { font-size: 8pt; color: #6b7280; margin-bottom: 8px; }
                .field-row { margin-bottom: 14px; }
                .field-label { font-size: 8pt; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 3px; }
                .field-value { font-size: 10pt; color: #1C1C1E; padding: 4px 0; border-bottom: 1px solid #e5e7eb; min-height: 20px; word-break: break-word; }
                .field-value.empty { color: #9ca3af; font-style: italic; }
                .field-value a { color: #B4864E; text-decoration: underline; }
                .signature-img { max-width: 250px; max-height: 80px; border: 1px solid #e5e7eb; border-radius: 4px; margin-top: 4px; }
                .checkbox-item { margin-bottom: 4px; font-size: 9pt; }
                .checkbox-checked { color: #B4864E; font-weight: 700; }
                .checkbox-unchecked { color: #d1d5db; }
                .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; text-align: center; font-size: 7pt; color: #9ca3af; }
                @media print { body { padding: 15px; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Arco Seguros</h1>
                <h2><?= $formName ?></h2>
                <p>Formulario llenado &mdash; <?= $today ?></p>
            </div>

            <table class="meta-table">
                <tr><td>No. de envío</td><td><?= $subNumber ?></td></tr>
                <tr><td>Fecha de envío</td><td><?= $submittedAt ?></td></tr>
                <tr><td>Estado</td><td><span class="status-badge"><?= $status ?></span></td></tr>
                <tr><td>Usuario</td><td><?= $userName ?></td></tr>
                <tr><td>Correo</td><td><?= $userEmail ?></td></tr>
                <tr><td>Versión</td><td>v<?= $version ?></td></tr>
            </table>

            <?php foreach ($structure as $section): ?>
                <?php if (!empty($section['section_name'])): ?>
                    <div class="section-title"><?= htmlspecialchars($section['section_name']) ?></div>
                <?php endif; ?>
                <?php if (!empty($section['section_description'])): ?>
                    <p class="section-desc"><?= htmlspecialchars($section['section_description']) ?></p>
                <?php endif; ?>

                <?php foreach ($section['fields'] as $field): ?>
                    <?php if ($field['is_visible'] == 0) continue; ?>
                    <?php $fieldId = $field['id_form_field']; ?>
                    <?php $fieldKey = $field['field_key'] ?? $field['field_type_name'] ?? 'text'; ?>
                    <?php $val = $valuesMap[$fieldId] ?? null; ?>
                    <?php $rawValue = $val ? ($val['field_value'] ?? '') : ''; ?>

                    <?php if ($fieldKey === 'heading'): ?>
                        <div style="font-size:9pt;font-weight:700;margin:10px 0 4px;"><?= htmlspecialchars($field['label']) ?></div>
                    <?php elseif ($fieldKey === 'checkbox'): ?>
                        <div class="field-row">
                            <div class="field-label"><?= htmlspecialchars($field['label']) ?> <?= $field['is_required'] ? '*' : '' ?></div>
                            <?php
                            $checkedValues = array_map('trim', explode(',', $rawValue));
                            ?>
                            <?php foreach (($field['options'] ?? []) as $opt): ?>
                                <?php
                                $optVal = $opt['option_value'] ?? $opt['option_label'];
                                $isChecked = in_array($optVal, $checkedValues) || in_array($opt['option_label'], $checkedValues);
                                ?>
                                <div class="checkbox-item">
                                    <?php if ($isChecked): ?>
                                        <span class="checkbox-checked">&#9745;</span> <?= htmlspecialchars($opt['option_label']) ?>
                                    <?php else: ?>
                                        <span class="checkbox-unchecked">&#9744;</span> <?= htmlspecialchars($opt['option_label']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($fieldKey === 'radio'): ?>
                        <div class="field-row">
                            <div class="field-label"><?= htmlspecialchars($field['label']) ?> <?= $field['is_required'] ? '*' : '' ?></div>
                            <?php foreach (($field['options'] ?? []) as $opt): ?>
                                <?php
                                $optVal = $opt['option_value'] ?? $opt['option_label'];
                                $isChecked = ($rawValue === $optVal || $rawValue === $opt['option_label']);
                                ?>
                                <div class="checkbox-item">
                                    <?php if ($isChecked): ?>
                                        <span class="checkbox-checked">&#9745;</span> <?= htmlspecialchars($opt['option_label']) ?>
                                    <?php else: ?>
                                        <span class="checkbox-unchecked">&#9744;</span> <?= htmlspecialchars($opt['option_label']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($fieldKey === 'signature'): ?>
                        <div class="field-row">
                            <div class="field-label"><?= htmlspecialchars($field['label']) ?> <?= $field['is_required'] ? '*' : '' ?></div>
                            <?php if (!empty($rawValue) && preg_match('#^data:image#', $rawValue)): ?>
                                <img src="<?= $rawValue ?>" class="signature-img" alt="Firma">
                            <?php elseif (!empty($rawValue)): ?>
                                <div class="field-value"><?= htmlspecialchars($rawValue) ?></div>
                            <?php else: ?>
                                <div class="field-value empty">---</div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($fieldKey === 'file'): ?>
                        <div class="field-row">
                            <div class="field-label"><?= htmlspecialchars($field['label']) ?> <?= $field['is_required'] ? '*' : '' ?></div>
                            <?php $att = $attachmentsMap[$fieldId] ?? null; ?>
                            <?php if ($att && !empty($att['file_url'])): ?>
                                <div class="field-value"><a href="<?= htmlspecialchars($att['file_url']) ?>" target="_blank"><?= htmlspecialchars($att['file_name']) ?></a></div>
                            <?php elseif (!empty($rawValue) && filter_var($rawValue, FILTER_VALIDATE_URL)): ?>
                                <div class="field-value"><a href="<?= htmlspecialchars($rawValue) ?>" target="_blank">Archivo adjunto</a></div>
                            <?php elseif (!empty($rawValue)): ?>
                                <div class="field-value"><?= htmlspecialchars($rawValue) ?></div>
                            <?php else: ?>
                                <div class="field-value empty">---</div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="field-row">
                            <div class="field-label"><?= htmlspecialchars($field['label']) ?> <?= $field['is_required'] ? '*' : '' ?></div>
                            <?php if (!empty($rawValue)): ?>
                                <div class="field-value"><?= nl2br(htmlspecialchars($rawValue)) ?></div>
                            <?php else: ?>
                                <div class="field-value empty">---</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($field['help_text'])): ?>
                        <div style="font-size:7pt;color:#9ca3af;font-style:italic;margin-top:2px;margin-bottom:8px;"><?= htmlspecialchars($field['help_text']) ?></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <div class="footer">
                Arco Seguros &middot; <?= $today ?> &middot; Envío <?= $subNumber ?>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
