<?php
if (file_exists(__DIR__ . '/installed.lock')) {
    header('Location: /admin/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Encartes Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .installer-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 0 auto;
        }
        .steps {
            display: flex;
            justify-content: space-between;
            padding: 30px;
            border-bottom: 1px solid #e9ecef;
        }
        .step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 10px;
        }
        .step.active .step-number {
            background: #2563eb;
            color: white;
        }
        .step.completed .step-number {
            background: #10b981;
            color: white;
        }
        .step-title {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .step.active .step-title {
            color: #2563eb;
            font-weight: bold;
        }
        .step-content {
            padding: 40px;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .loading-spinner {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="installer-card">
            <div class="steps">
                <div class="step active" id="step-1">
                    <div class="step-number">1</div>
                    <div class="step-title">Banco de Dados</div>
                </div>
                <div class="step" id="step-2">
                    <div class="step-number">2</div>
                    <div class="step-title">Aplicação</div>
                </div>
                <div class="step" id="step-3">
                    <div class="step-number">3</div>
                    <div class="step-title">Mercado Pago</div>
                </div>
                <div class="step" id="step-4">
                    <div class="step-number">4</div>
                    <div class="step-title">Instalar</div>
                </div>
            </div>

            <div class="step-content">
                <div id="alert-area"></div>

                <form id="install-form">
                    <div id="step-1-content">
                        <h4 class="mb-4">
                            <i class="bi bi-database text-primary"></i> Configuração do Banco de Dados
                        </h4>
                        <div class="mb-3">
                            <label class="form-label">Host do Banco</label>
                            <input type="text" name="db_host" class="form-control" value="localhost" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome do Banco de Dados</label>
                            <input type="text" name="db_name" class="form-control" required placeholder="encartes_pro">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuário do Banco</label>
                            <input type="text" name="db_user" class="form-control" required placeholder="root">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha do Banco</label>
                            <input type="password" name="db_pass" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prefixo das Tabelas</label>
                            <input type="text" name="db_prefix" class="form-control" value="enc_" placeholder="enc_">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="testConnection()">
                            <i class="bi bi-plug"></i> Testar Conexão
                        </button>
                    </div>

                    <div id="step-2-content" style="display: none;">
                        <h4 class="mb-4">
                            <i class="bi bi-gear text-primary"></i> Configuração da Aplicação
                        </h4>
                        <div class="mb-3">
                            <label class="form-label">URL Base da Aplicação</label>
                            <input type="url" name="app_url" class="form-control" required 
                                   placeholder="https://meusite.com" value="http://localhost/encartes">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome do Sistema</label>
                            <input type="text" name="app_name" class="form-control" required 
                                   placeholder="Encartes Pro" value="Encartes Pro">
                        </div>
                        <hr>
                        <h5 class="mb-3">Dados do Administrador</h5>
                        <div class="mb-3">
                            <label class="form-label">Nome do Administrador</label>
                            <input type="text" name="admin_name" class="form-control" required placeholder="Administrador">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-mail do Administrador</label>
                            <input type="email" name="admin_email" class="form-control" required placeholder="admin@email.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha do Administrador</label>
                            <input type="password" name="admin_senha" class="form-control" required minlength="6" placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Senha</label>
                            <input type="password" name="admin_senha_confirm" class="form-control" required>
                        </div>
                    </div>

                    <div id="step-3-content" style="display: none;">
                        <h4 class="mb-4">
                            <i class="bi bi-credit-card text-primary"></i> Configuração do Mercado Pago
                        </h4>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Você pode obter suas chaves no 
                            <a href="https://www.mercadopago.com.br/developers/panel" target="_blank">Painel do Mercado Pago</a>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Public Key</label>
                            <input type="text" name="mp_public_key" class="form-control" placeholder="APP_USR-...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Access Token</label>
                            <input type="text" name="mp_access_token" class="form-control" placeholder="APP_USR-...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modo de Operação</label>
                            <select name="mp_modo" class="form-select">
                                <option value="sandbox">Sandbox (Teste)</option>
                                <option value="production">Produção</option>
                            </select>
                        </div>
                    </div>

                    <div id="step-4-content" style="display: none;">
                        <h4 class="mb-4">
                            <i class="bi bi-rocket text-primary"></i> Instalar Sistema
                        </h4>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Esta operação irá criar todas as tabelas no banco de dados e configurar o sistema.
                        </div>
                        <div class="text-center py-4">
                            <div class="loading-spinner" id="loading">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                                <p class="mb-0">Instalando sistema...</p>
                            </div>
                            <div id="success-result" style="display: none;">
                                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                                <h5 class="mt-3 text-success">Instalação Concluída!</h5>
                                <div class="text-start bg-light p-4 rounded mt-3">
                                    <p class="mb-2"><strong>Painel Admin:</strong> <a id="admin-url" href="#"></a></p>
                                    <p class="mb-2"><strong>E-mail:</strong> <span id="admin-email"></span></p>
                                    <p class="mb-0"><strong>Senha:</strong> A senha definida na instalação</p>
                                </div>
                                <a href="/admin/" class="btn btn-success mt-3">
                                    <i class="bi bi-box-arrow-in-right"></i> Acessar Painel Admin
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4" id="nav-buttons">
                        <button type="button" class="btn btn-outline-secondary" id="btn-back" onclick="prevStep()" style="display: none;">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </button>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-primary" id="btn-next" onclick="nextStep()">
                                Próximo <i class="bi bi-arrow-right"></i>
                            </button>
                            <button type="button" class="btn btn-success" id="btn-install" onclick="install()" style="display: none;">
                                <i class="bi bi-check-lg"></i> INSTALAR
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let dbConnected = false;

        function updateSteps() {
            for (let i = 1; i <= totalSteps; i++) {
                const step = document.getElementById('step-' + i);
                if (i < currentStep) {
                    step.className = 'step completed';
                } else if (i === currentStep) {
                    step.className = 'step active';
                } else {
                    step.className = 'step';
                }
            }

            document.querySelectorAll('[id$="-content"]').forEach(el => el.style.display = 'none');
            document.getElementById('step-' + currentStep + '-content').style.display = 'block';

            document.getElementById('btn-back').style.display = currentStep > 1 ? 'block' : 'none';
            document.getElementById('btn-next').style.display = currentStep < totalSteps ? 'block' : 'none';
            document.getElementById('btn-install').style.display = currentStep === totalSteps ? 'block' : 'none';
        }

        function showAlert(type, message) {
            const colors = {
                success: 'alert-success',
                error: 'alert-danger',
                warning: 'alert-warning',
                info: 'alert-info'
            };
            document.getElementById('alert-area').innerHTML = 
                '<div class="alert ' + colors[type] + ' alert-dismissible fade show">' + message + '</div>';
        }

        function nextStep() {
            if (currentStep === 1 && !dbConnected) {
                showAlert('warning', 'Teste a conexão com o banco de dados primeiro');
                return;
            }
            if (currentStep === 2) {
                const senha = document.querySelector('[name="admin_senha"]').value;
                const confirmar = document.querySelector('[name="admin_senha_confirm"]').value;
                if (senha !== confirmar) {
                    showAlert('error', 'As senhas não conferem');
                    return;
                }
            }
            if (currentStep < totalSteps) {
                currentStep++;
                updateSteps();
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateSteps();
            }
        }

        async function testConnection() {
            const form = document.getElementById('install-form');
            const formData = new FormData();
            formData.append('acao', 'testar_conexao');
            formData.append('db_host', form.querySelector('[name="db_host"]').value);
            formData.append('db_name', form.querySelector('[name="db_name"]').value);
            formData.append('db_user', form.querySelector('[name="db_user"]').value);
            formData.append('db_pass', form.querySelector('[name="db_pass"]').value);

            try {
                const response = await fetch('installer.php', { method: 'POST', body: formData });
                const data = await response.json();
                
                if (data.sucesso) {
                    dbConnected = true;
                    showAlert('success', '<i class="bi bi-check-circle"></i> ' + data.mensagem);
                    nextStep();
                } else {
                    dbConnected = false;
                    showAlert('error', '<i class="bi bi-x-circle"></i> ' + data.erro);
                }
            } catch (e) {
                showAlert('error', 'Erro ao testar conexão');
            }
        }

        async function install() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('btn-install').disabled = true;

            const form = document.getElementById('install-form');
            const formData = new FormData();
            formData.append('acao', 'instalar');

            const fields = ['db_host', 'db_name', 'db_user', 'db_pass', 'db_prefix', 
                           'app_url', 'app_name', 'admin_name', 'admin_email', 'admin_senha',
                           'mp_public_key', 'mp_access_token', 'mp_modo'];
            
            fields.forEach(field => {
                formData.append(field, form.querySelector('[name="' + field + '"]').value);
            });

            try {
                const response = await fetch('installer.php', { method: 'POST', body: formData });
                const data = await response.json();

                document.getElementById('loading').style.display = 'none';

                if (data.sucesso) {
                    document.getElementById('success-result').style.display = 'block';
                    document.getElementById('admin-url').textContent = data.admin_url;
                    document.getElementById('admin-url').href = data.admin_url;
                    document.getElementById('admin-email').textContent = data.admin_email;
                    document.getElementById('nav-buttons').style.display = 'none';
                } else {
                    showAlert('error', data.erro);
                    document.getElementById('btn-install').disabled = false;
                }
            } catch (e) {
                document.getElementById('loading').style.display = 'none';
                showAlert('error', 'Erro na instalação: ' + e.message);
                document.getElementById('btn-install').disabled = false;
            }
        }
    </script>
</body>
</html>