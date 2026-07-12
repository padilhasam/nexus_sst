<?php

class RiscosController extends Controller{
   
    private $riscoModel;

    private array $categorias = [
        'fisicos' => [
            'banco' => 'fisico',
            'titulo' => 'Riscos Físicos',
            'icone' => 'fa-bolt',
            'cor' => '#198754'
        ],
        'quimicos' => [
            'banco' => 'quimico',
            'titulo' => 'Riscos Químicos',
            'icone' => 'fa-flask',
            'cor' => '#dc3545'
        ],
        'biologicos' => [
            'banco' => 'biologico',
            'titulo' => 'Riscos Biológicos',
            'icone' => 'fa-virus',
            'cor' => '#795548'
        ],
        'ergonomicos' => [
            'banco' => 'ergonomico',
            'titulo' => 'Riscos Ergonômicos',
            'icone' => 'fa-person-walking',
            'cor' => '#ffc107'
        ],
        'acidentes' => [
            'banco' => 'acidente',
            'titulo' => 'Riscos de Acidentes',
            'icone' => 'fa-triangle-exclamation',
            'cor' => '#0d6efd'
        ],
        'psicossociais' => [
            'banco' => 'psicossocial',
            'titulo' => 'Riscos Psicossociais',
            'icone' => 'fa-brain',
            'cor' => '#6c757d'
        ]
    ];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->riscoModel = $this->model('Risco');
    }

    public function index()
    {
        $this->view('riscos/index', [
            'categorias' => $this->categorias
        ]);
    }

    public function fisicos()
    {
        $this->listarCategoria('fisicos');
    }

    public function quimicos()
    {
        $this->listarCategoria('quimicos');
    }

    public function biologicos()
    {
        $this->listarCategoria('biologicos');
    }

    public function ergonomicos()
    {
        $this->listarCategoria('ergonomicos');
    }

    public function acidentes()
    {
        $this->listarCategoria('acidentes');
    }

    public function psicossociais()
    {
        $this->listarCategoria('psicossociais');
    }

    private function listarCategoria(string $slug)
    {
        if (!$this->categoriaExiste($slug)) {
            $_SESSION['erro'] = 'Categoria de risco inválida.';
            header('Location: ' . BASE_URL . '/riscos');
            exit;
        }

        $meta = $this->categorias[$slug];

       $riscos = $this->riscoModel->listarPorCategoria($meta['banco']);

        $this->view('riscos/listar', [
            'riscos' => $riscos,
            'categoria' => $slug,
            'titulo' => $meta['titulo'],
            'icone' => $meta['icone'],
            'cor' => $meta['cor']
        ]);
    }

    public function listar($categoria)
    {
        if (!$this->categoriaExiste($categoria)) {
            $_SESSION['erro'] = 'Categoria de risco inválida.';
            header('Location: ' . BASE_URL . '/riscos');
            exit;
        }

        $meta = $this->categorias[$categoria];

       $riscos = $this->riscoModel->listarPorCategoria($meta['banco']);

        $this->view('riscos/listar', [
            'riscos' => $riscos,
            'categoria' => $categoria,
            'titulo' => $meta['titulo'],
            'icone' => $meta['icone'],
            'cor' => $meta['cor'],
        ]);
    }

    public function criar($categoria)
    {
        if (!$this->categoriaExiste($categoria)) {
            $_SESSION['erro'] = 'Categoria de risco inválida.';
            header('Location: ' . BASE_URL . '/riscos');
            exit;
        }

        $meta = $this->categorias[$categoria];

       $this->view('riscos/criar', [
            'categoria' => $meta['banco'],      // valor que será salvo no banco
            'categoria_url' => $categoria,      // slug da URL
            'titulo' => 'Cadastrar ' . $meta['titulo'],
            'icone' => $meta['icone'],
            'cor' => $meta['cor'],
        ]);
    }

    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/riscos');
            exit;
        }

        $dados = $this->montarDadosFormulario();

        $slug = $dados['categoria'];

        if (!$this->categoriaExiste($slug)) {
            $_SESSION['erro'] = 'Categoria de risco inválida.';
            header('Location: ' . BASE_URL . '/riscos');
            exit;
        }

        $dados['categoria'] = $this->categorias[$slug]['banco'];

        if (empty($dados['nome'])) {
            $_SESSION['erro'] = 'O nome do risco é obrigatório.';
            header('Location: ' . BASE_URL . '/riscos/criar/' . $dados['categoria']);
            exit;
        }

        if (!empty($dados['codigo'])) {
            $riscoExistente = $this->riscoModel->buscarPorCodigo($dados['codigo']);

            if ($riscoExistente) {
                $_SESSION['erro'] = 'Já existe um risco cadastrado com este código interno.';
                header('Location: ' . BASE_URL . '/riscos/criar/' . $dados['categoria']);
                exit;
            }
        }

        $riscoId = $this->riscoModel->salvar($dados);

        $_SESSION['sucesso'] = $riscoId
            ? 'Risco cadastrado com sucesso!'
            : 'Erro ao cadastrar risco.';

        header('Location: ' . BASE_URL . '/riscos/listar/' . $slug);
        exit;
    }

    private function buscarSlugPorCategoriaBanco(string $categoriaBanco): string
    {
        foreach ($this->categorias as $slug => $dados) {
            if ($dados['banco'] === $categoriaBanco) {
                return $slug;
            }
        }

        return 'fisicos';
    }

    public function editar($id)
    {
        $risco = $this->riscoModel->buscarPorId((int)$id);

        if (!$risco) {
            $_SESSION['erro'] = 'Risco não encontrado.';
            header('Location: ' . BASE_URL . '/riscos');
            exit;
        }

        // Converte categoria do banco (fisico) para slug da URL (fisicos)
        $slug = $this->buscarSlugPorCategoriaBanco($risco['categoria']);

        $meta = $this->categorias[$slug];

        $this->view('riscos/editar', [
            'risco'     => $risco,
            'categoria' => $slug,
            'titulo'    => 'Editar Risco',
            'icone'     => $meta['icone'],
            'cor'       => $meta['cor'],
        ]);
    }

    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/riscos');
            exit;
        }

        $id = (int)$id;
        $dados = $this->montarDadosFormulario();

        $slug = $dados['categoria'];

        if (!$this->categoriaExiste($slug)) {
            $_SESSION['erro'] = 'Categoria de risco inválida.';
            header('Location: ' . BASE_URL . '/riscos');
            exit;
        }

        $dados['categoria'] = $this->categorias[$slug]['banco'];

        if (empty($dados['nome'])) {
            $_SESSION['erro'] = 'O nome do risco é obrigatório.';
            header('Location: ' . BASE_URL . '/riscos/editar/' . $id);
            exit;
        }

        if (!empty($dados['codigo'])) {
            $riscoExistente = $this->riscoModel->buscarPorCodigo($dados['codigo']);

            if ($riscoExistente && (int)$riscoExistente['id'] !== $id) {
                $_SESSION['erro'] = 'Já existe outro risco cadastrado com este código interno.';
                header('Location: ' . BASE_URL . '/riscos/editar/' . $id);
                exit;
            }
        }

        $this->riscoModel->atualizar($id, $dados);

        $_SESSION['sucesso'] = 'Risco atualizado com sucesso!';

        header('Location: ' . BASE_URL . '/riscos/listar/' . $slug);
        exit;
    }

    public function excluir($id)
    {
        $risco = $this->riscoModel->buscarPorId((int)$id);

        if (!$risco) {
            $_SESSION['erro'] = 'Risco não encontrado.';
            header('Location: ' . BASE_URL . '/riscos');
            exit;
        }

        $_SESSION['sucesso'] = $this->riscoModel->desativar((int)$id)
            ? 'Risco desativado com sucesso!'
            : 'Erro ao desativar risco.';

        // Converte categoria do banco (fisico) para slug da URL (fisicos)
        $slug = $this->buscarSlugPorCategoriaBanco($risco['categoria']);

        header('Location: ' . BASE_URL . '/riscos/listar/' . $slug);
        exit;
    }

    private function montarDadosFormulario(): array
    {
        $categoriaUrl = trim($_POST['categoria'] ?? '');

        $categoria = array_key_exists($categoriaUrl, $this->categorias)
            ? $categoriaUrl
            : $this->buscarSlugPorCategoriaBanco($categoriaUrl);

        return [
            'codigo' => !empty($_POST['codigo'])
                ? strtoupper(trim($_POST['codigo']))
                : 'RIS-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),

            'codigo_externo' => !empty($_POST['codigo_externo'])
                ? strtoupper(trim($_POST['codigo_externo']))
                : 'EXT-RIS-' . date('YmdHis'),

            'categoria' => $categoria,

            'nome' => trim($_POST['nome'] ?? ''),

            'tipo_avaliacao' => !empty($_POST['tipo_avaliacao'])
                ? trim($_POST['tipo_avaliacao'])
                : 'Qualitativo',

            'descricao' => !empty($_POST['descricao'])
                ? trim($_POST['descricao'])
                : null,

            'normas_aplicaveis' => !empty($_POST['normas_aplicaveis'])
                ? trim($_POST['normas_aplicaveis'])
                : null,

            'metodologia' => !empty($_POST['metodologia'])
                ? trim($_POST['metodologia'])
                : null,

            'unidade_medida' => !empty($_POST['unidade_medida'])
                ? trim($_POST['unidade_medida'])
                : null,

            'limite_nr15' => !empty($_POST['limite_nr15'])
                ? trim($_POST['limite_nr15'])
                : null,

            'limite_acgih' => !empty($_POST['limite_acgih'])
                ? trim($_POST['limite_acgih'])
                : null,

            'nivel_acao' => !empty($_POST['nivel_acao'])
                ? trim($_POST['nivel_acao'])
                : null,

            'exige_quantificacao' => isset($_POST['exige_quantificacao'])
                ? (int)$_POST['exige_quantificacao']
                : 0,

            'severidade_padrao' => !empty($_POST['severidade_padrao'])
                ? (int)$_POST['severidade_padrao']
                : 1,

            'probabilidade_padrao' => !empty($_POST['probabilidade_padrao'])
                ? (int)$_POST['probabilidade_padrao']
                : 1,

            'ativo' => isset($_POST['ativo'])
                ? (int)$_POST['ativo']
                : 0
        ];
    }

    private function categoriaExiste(string $categoria): bool
    {
        return array_key_exists($categoria, $this->categorias);
    }
}