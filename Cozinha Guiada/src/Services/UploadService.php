<?php
// ==============================================================================
// PROJETO: Cozinha Guiada
// ARQUIVO: src/Services/UploadService.php
// DESCRIÇÃO: Serviço de validação e upload seguro de imagens (RF35, RF36, RNF11)
// ==============================================================================

require_once CONFIG_PATH . '/constants.php';

class UploadService {
    private int $maxSize;
    private array $allowedMimes;
    private string $uploadDir;

    public function __construct() {
        $this->maxSize = (int)env('UPLOAD_MAX_SIZE', 5242880); // 5MB em bytes (RF35)
        
        $allowedEnv = env('ALLOWED_IMAGE_TYPES', 'image/jpeg,image/png,image/webp');
        $this->allowedMimes = array_map('trim', explode(',', $allowedEnv));
        
        $this->uploadDir = RECIPE_UPLOADS_PATH;
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Valida e move o arquivo enviado com proteção contra upload de scripts maliciosos (RNF11)
     *
     * @param array $file Entrada de $_FILES['imagem']
     * @return array Resultado com 'success' e 'path' (caminho público relativo) ou 'error'
     */
    public function uploadRecipeImage(array $file): array {
        // Verifica se houve envio
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'Nenhum arquivo enviado.'];
        }

        // Verifica erros padrão do PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Erro durante o upload do arquivo (Código: ' . $file['error'] . ').'];
        }

        // Validação de tamanho máximo (5MB - RF35)
        if ($file['size'] > $this->maxSize) {
            $maxMb = round($this->maxSize / 1024 / 1024);
            return ['success' => false, 'error' => "A imagem excede o tamanho máximo permitido de {$maxMb}MB."];
        }

        // Validação estrita de tipo MIME inspecionando os bytes reais do arquivo (não apenas a extensão)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $this->allowedMimes, true)) {
            return ['success' => false, 'error' => 'Formato de imagem inválido. Aceitos apenas JPG, PNG e WEBP.'];
        }

        // Mapeamento seguro de extensões autorizadas
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];
        $ext = $extensions[$mime] ?? 'jpg';

        // Geração de nome criptograficamente seguro com hash aleatório para evitar colisão e execução
        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = $this->uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Falha ao salvar a imagem no diretório de destino.'];
        }

        // Caminho relativo para armazenamento no banco de dados e uso nas views
        $publicPath = '/uploads/receitas/' . $fileName;

        return [
            'success'   => true,
            'file_name' => $fileName,
            'path'      => $publicPath
        ];
    }
}
