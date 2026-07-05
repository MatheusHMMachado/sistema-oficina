<?php
// ARQUIVO: routes/web.php
// Este arquivo contém TODAS as rotas do sistema

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\ServicoPecaController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\PortalClienteController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\AtribuicaoServicoController;

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS - PORTAL DO CLIENTE
|--------------------------------------------------------------------------
*/

Route::prefix('portal')->group(function () {
    // Consulta Pública por CPF (sem autenticação)
    Route::get('/consulta', [PortalClienteController::class, 'consultaPublica'])->name('portal.consulta-publica');
    Route::post('/consulta', [PortalClienteController::class, 'processarConsulta']);
    Route::get('/ordem/{id}/detalhes', [PortalClienteController::class, 'verOrdemPublica'])->name('portal.ordem.publica');
    
    // Login do Portal
    Route::get('/login', [PortalClienteController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [PortalClienteController::class, 'login']);
    
    // Primeiro Acesso
    Route::get('/primeiro-acesso', [PortalClienteController::class, 'primeiroAcesso'])->name('portal.primeiro-acesso');
    Route::post('/primeiro-acesso', [PortalClienteController::class, 'salvarPrimeiroAcesso']);
    
    // Rotas protegidas do portal (requerem login do cliente)
    Route::middleware(['portal.auth'])->group(function () {
        Route::get('/dashboard', [PortalClienteController::class, 'dashboard'])->name('portal.dashboard');
        Route::get('/veiculo/{id}/historico', [PortalClienteController::class, 'historicoVeiculo'])->name('portal.veiculo.historico');
        Route::get('/ordem/{id}', [PortalClienteController::class, 'verOrdem'])->name('portal.ordem.detalhes');
        Route::get('/agendar', [PortalClienteController::class, 'agendarServico'])->name('portal.agendar');
        Route::post('/agendar', [PortalClienteController::class, 'salvarAgendamento']);
        Route::get('/orcamento', [PortalClienteController::class, 'orcamento'])->name('portal.orcamento');
        Route::post('/orcamento', [PortalClienteController::class, 'enviarOrcamento']);
        Route::get('/lembretes', [PortalClienteController::class, 'lembretes'])->name('portal.lembretes');
        Route::post('/lembretes', [PortalClienteController::class, 'salvarLembretes']);
        Route::get('/historico', [PortalClienteController::class, 'historicoCompleto'])->name('portal.historico');
        Route::post('/logout', [PortalClienteController::class, 'logout'])->name('portal.logout');
    });
    
    // QR Code de acesso rápido
    Route::get('/qr/{token}', [PortalClienteController::class, 'acessoQR'])->name('portal.cliente.qr');
});

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS - SISTEMA ADMINISTRATIVO
|--------------------------------------------------------------------------
*/

// Rotas de Autenticação do Sistema
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

// Redirecionar raiz
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| ROTAS PROTEGIDAS - SISTEMA ADMINISTRATIVO
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'checkActive'])->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // GESTÃO DE CLIENTES
    Route::resource('clientes', ClienteController::class);
    Route::get('/api/clientes/{cliente}/veiculos', [ClienteController::class, 'veiculos']);
    Route::post('/clientes/{cliente}/ativar-portal', [ClienteController::class, 'ativarPortal'])->name('clientes.ativar-portal');
    Route::post('/clientes/{cliente}/enviar-acesso', [ClienteController::class, 'enviarAcessoPortal'])->name('clientes.enviar-acesso');
    
    // GESTÃO DE VEÍCULOS
    Route::resource('veiculos', VeiculoController::class);
    
    // GESTÃO DE ORDENS DE SERVIÇO
    Route::resource('ordens', OrdemServicoController::class)->parameters([
        'ordens' => 'ordem',
    ]);
    Route::patch('/ordens/{ordem}/status', [OrdemServicoController::class, 'updateStatus'])->name('ordens.update-status');
    Route::post('/ordens/{ordem}/adicionar-atualizacao', [OrdemServicoController::class, 'adicionarAtualizacaoStatus'])->name('ordens.adicionar-atualizacao');
    Route::post('/ordens/{ordem}/adicionar-item', [OrdemServicoController::class, 'adicionarItem'])->name('ordens.adicionar-item');
    Route::delete('/ordem-itens/{item}', [OrdemServicoController::class, 'removerItem'])->name('ordens.remover-item');
    Route::get('/ordens/{ordem}/pdf', [OrdemServicoController::class, 'pdf'])->name('ordens.pdf');
    Route::post('/ordens/{ordem}/notificar-cliente', [OrdemServicoController::class, 'notificarCliente'])->name('ordens.notificar-cliente');
    
    // GESTÃO DE SERVIÇOS E PEÇAS
    Route::resource('servicos-pecas', ServicoPecaController::class);
    Route::patch('/servicos-pecas/{servicoPeca}/toggle-status', [ServicoPecaController::class, 'toggleStatus'])->name('servicos-pecas.toggle-status');

    // ATRIBUIÇÕES DE SERVIÇO (admin define; mecânico registra progresso)
    Route::get('/atribuicoes', [AtribuicaoServicoController::class, 'index'])->name('atribuicoes.index');
    Route::post('/atribuicoes', [AtribuicaoServicoController::class, 'store'])->name('atribuicoes.store');
    Route::patch('/atribuicoes/{atribuicao}/progresso', [AtribuicaoServicoController::class, 'atualizarProgresso'])->name('atribuicoes.progresso');
    
    // CHECKLIST
    Route::get('/checklist', [ChecklistController::class, 'index'])->name('checklist.index');
    Route::get('/checklist/{ordem}', [ChecklistController::class, 'show'])->name('checklist.show');
    Route::post('/checklist/{ordem}', [ChecklistController::class, 'store'])->name('checklist.store');
    Route::delete('/checklist/{checklist}', [ChecklistController::class, 'destroy'])->name('checklist.destroy');
    Route::delete('/checklist-foto/{foto}', [ChecklistController::class, 'destroyFoto'])->name('checklist.foto.destroy');
    
    // GESTÃO DE FUNCIONÁRIOS (APENAS ADMIN)
    Route::middleware(['admin'])->group(function () {
        Route::get('/funcionarios', [FuncionarioController::class, 'index'])->name('funcionarios.index');
        Route::post('/funcionarios', [FuncionarioController::class, 'store'])->name('funcionarios.store');
        Route::get('/funcionarios/{id}/edit', [FuncionarioController::class, 'edit'])->name('funcionarios.edit');
        Route::put('/funcionarios/{id}', [FuncionarioController::class, 'update'])->name('funcionarios.update');
        Route::get('/funcionarios/{id}/toggle-status', [FuncionarioController::class, 'toggleStatus'])->name('funcionarios.toggle-status');
        Route::get('/funcionarios/{id}/delete', [FuncionarioController::class, 'destroy'])->name('funcionarios.destroy');
        
        // Relatórios
        Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('/relatorios/financeiro', [RelatorioController::class, 'financeiro'])->name('relatorios.financeiro');
        Route::get('/relatorios/servicos', [RelatorioController::class, 'servicos'])->name('relatorios.servicos');
        Route::get('/relatorios/clientes', [RelatorioController::class, 'clientes'])->name('relatorios.clientes');
    });
    
    // API para verificação
    Route::get('/api/check-auth', [AuthController::class, 'checkAuth'])->name('api.check-auth');
    
    // Notificações
    Route::get('/notificacoes', [NotificacaoController::class, 'index'])->name('notificacoes.index');
    Route::post('/notificacoes/{id}/marcar-lida', [NotificacaoController::class, 'marcarLida'])->name('notificacoes.marcar-lida');
});