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
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\AiCopilotoController;
use App\Http\Controllers\TemaController;
use App\Http\Controllers\AgendamentoController;

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS - PORTAL DO CLIENTE
|--------------------------------------------------------------------------
*/

Route::prefix('portal')->group(function () {
    // Entrada única do Portal do Cliente
    Route::get('/', [PortalClienteController::class, 'home'])->name('portal.home');

    // Compatibilidade: consulta e login apontam para o mesmo portal
    Route::get('/consulta', [PortalClienteController::class, 'consultaPublica'])->name('portal.consulta-publica');
    Route::post('/consulta', [PortalClienteController::class, 'processarConsulta']);
    Route::get('/ordem/{id}/detalhes', [PortalClienteController::class, 'verOrdemPublica'])->name('portal.ordem.publica');

    // Agendamento público (com ou sem cadastro)
    Route::get('/agendar', [PortalClienteController::class, 'agendarServico'])->name('portal.agendar');
    Route::post('/agendar', [PortalClienteController::class, 'salvarAgendamento']);
    
    Route::get('/login', [PortalClienteController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [PortalClienteController::class, 'login']);
    
    Route::get('/primeiro-acesso', [PortalClienteController::class, 'primeiroAcesso'])->name('portal.primeiro-acesso');
    Route::post('/primeiro-acesso', [PortalClienteController::class, 'salvarPrimeiroAcesso']);
    
    Route::middleware(['portal.auth'])->group(function () {
        Route::get('/dashboard', [PortalClienteController::class, 'dashboard'])->name('portal.dashboard');
        Route::get('/veiculo/{id}/historico', [PortalClienteController::class, 'historicoVeiculo'])->name('portal.veiculo.historico');
        Route::get('/ordem/{id}', [PortalClienteController::class, 'verOrdem'])->name('portal.ordem.detalhes');
        Route::post('/ordem/{id}/avaliacao', [PortalClienteController::class, 'salvarAvaliacao'])->name('portal.avaliacao');
        Route::get('/orcamento', [PortalClienteController::class, 'orcamento'])->name('portal.orcamento');
        Route::post('/orcamento', [PortalClienteController::class, 'enviarOrcamento']);
        Route::get('/lembretes', [PortalClienteController::class, 'lembretes'])->name('portal.lembretes');
        Route::post('/lembretes', [PortalClienteController::class, 'salvarLembretes']);
        Route::get('/historico', [PortalClienteController::class, 'historicoCompleto'])->name('portal.historico');
        Route::post('/logout', [PortalClienteController::class, 'logout'])->name('portal.logout');
    });
    
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
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

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

    // Copiloto de IA (chat flutuante)
    Route::get('/copiloto/status', [AiCopilotoController::class, 'status'])->name('copiloto.status');
    Route::post('/copiloto/chat', [AiCopilotoController::class, 'chat'])->name('copiloto.chat');
    
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
    Route::resource('servicos-pecas', ServicoPecaController::class)->parameters([
        'servicos-pecas' => 'servicoPeca',
    ]);
    Route::patch('/servicos-pecas/{servicoPeca}/toggle-status', [ServicoPecaController::class, 'toggleStatus'])->name('servicos-pecas.toggle-status');

    // CHECKLIST
    Route::get('/checklist', [ChecklistController::class, 'index'])->name('checklist.index');
    Route::get('/checklist/{ordem}', [ChecklistController::class, 'show'])->name('checklist.show');
    Route::post('/checklist/{ordem}', [ChecklistController::class, 'store'])->name('checklist.store');
    Route::delete('/checklist/{checklist}', [ChecklistController::class, 'destroy'])->name('checklist.destroy');
    Route::delete('/checklist-foto/{foto}', [ChecklistController::class, 'destroyFoto'])->name('checklist.foto.destroy');

    // ATRIBUIÇÕES — admin cria/exclui/aprova; mecânico visualiza e atualiza as próprias
    Route::get('/atribuicoes', [AtribuicaoServicoController::class, 'index'])->name('atribuicoes.index');
    Route::patch('/atribuicoes/{atribuicao}/progresso', [AtribuicaoServicoController::class, 'atualizarProgresso'])->name('atribuicoes.progresso');

    Route::middleware(['admin'])->group(function () {
        Route::get('/configuracoes/tema', [TemaController::class, 'edit'])->name('configuracoes.tema');
        Route::put('/configuracoes/tema', [TemaController::class, 'update'])->name('configuracoes.tema.update');

        Route::get('/agendamentos', [AgendamentoController::class, 'index'])->name('agendamentos.index');
        Route::get('/agendamentos/carga-mecanicos', [AgendamentoController::class, 'cargaMecanicos'])->name('agendamentos.carga-mecanicos');
        Route::post('/agendamentos/{agendamento}/status', [AgendamentoController::class, 'atualizarStatus'])->name('agendamentos.status');

        Route::post('/atribuicoes', [AtribuicaoServicoController::class, 'store'])->name('atribuicoes.store');
        Route::delete('/atribuicoes/{atribuicao}', [AtribuicaoServicoController::class, 'destroy'])->name('atribuicoes.destroy');
        Route::post('/atribuicoes/{atribuicao}/reagendar/aprovar', [AtribuicaoServicoController::class, 'aprovarReagendamento'])->name('atribuicoes.reagendar.aprovar');
        Route::post('/atribuicoes/{atribuicao}/reagendar/recusar', [AtribuicaoServicoController::class, 'recusarReagendamento'])->name('atribuicoes.reagendar.recusar');

        Route::get('/funcionarios', [FuncionarioController::class, 'index'])->name('funcionarios.index');
        Route::post('/funcionarios', [FuncionarioController::class, 'store'])->name('funcionarios.store');
        Route::get('/funcionarios/{id}/edit', [FuncionarioController::class, 'edit'])->name('funcionarios.edit');
        Route::get('/funcionarios/{id}/json', [FuncionarioController::class, 'showJson'])->name('funcionarios.json');
        Route::put('/funcionarios/{id}', [FuncionarioController::class, 'update'])->name('funcionarios.update');
        Route::post('/funcionarios/{id}/toggle-status', [FuncionarioController::class, 'toggleStatus'])->name('funcionarios.toggle-status');
        Route::delete('/funcionarios/{id}', [FuncionarioController::class, 'destroy'])->name('funcionarios.destroy');
        // Compatibilidade com links antigos (GET)
        Route::get('/funcionarios/{id}/toggle-status', [FuncionarioController::class, 'toggleStatus']);
        Route::get('/funcionarios/{id}/delete', [FuncionarioController::class, 'destroy']);
        
        // Controle Financeiro
        Route::get('/financeiro', [FinanceiroController::class, 'index'])->name('financeiro.index');
        Route::get('/financeiro/relatorio-mensal', [FinanceiroController::class, 'relatorioMensalPdf'])->name('financeiro.relatorio-mensal');
        Route::post('/financeiro/despesas', [FinanceiroController::class, 'storeDespesa'])->name('financeiro.despesas.store');
        Route::delete('/financeiro/despesas/{despesa}', [FinanceiroController::class, 'destroyDespesa'])->name('financeiro.despesas.destroy');
        Route::patch('/financeiro/ordens/{ordem}/pagamento', [FinanceiroController::class, 'atualizarPagamento'])->name('financeiro.pagamento');

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
    Route::post('/notificacoes/marcar-todas', [NotificacaoController::class, 'marcarTodasLidas'])->name('notificacoes.marcar-todas');
});