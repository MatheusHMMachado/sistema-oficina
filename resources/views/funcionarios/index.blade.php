<!-- ARQUIVO: resources/views/funcionarios/index.blade.php -->
<!-- Crie a pasta: resources/views/funcionarios/ -->

@extends('layouts.app')

@section('title', 'Funcionários - Sistema de Oficina')
@section('page-title', 'Gestão de Funcionários')

@section('content')
<div class="space-y-6">
    <!-- Header com Botões -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Funcionários</h1>
            <p class="text-gray-600">Gerencie o acesso dos funcionários ao sistema</p>
        </div>
        <button onclick="openModalFuncionario()" class="inline-flex items-center px-6 py-3 btn-primary text-white rounded-lg font-medium">
            <i class="fas fa-user-plus mr-2"></i>
            Novo Funcionário
        </button>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-xl font-bold">{{ $totalFuncionarios }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-600">Ativos</p>
                    <p class="text-xl font-bold text-green-600">{{ $funcionariosAtivos }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-shield text-yellow-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-600">Admins</p>
                    <p class="text-xl font-bold text-yellow-600">{{ $totalAdmins }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-purple-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-600">Online</p>
                    <p class="text-xl font-bold text-purple-600">{{ $funcionariosOnline }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Funcionários -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funcionário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cargo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Último Acesso</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($funcionarios as $funcionario)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img class="h-10 w-10 rounded-full" 
                                         src="https://ui-avatars.com/api/?name={{ urlencode($funcionario->name) }}&background={{ ltrim(($brand ?? \App\Models\ConfiguracaoSistema::atual())->cor_primaria ?? '#FF6B35', '#') }}&color=fff" 
                                         alt="">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $funcionario->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $funcionario->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $funcionario->role == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $funcionario->role == 'admin' ? 'Administrador' : 'Funcionário' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $funcionario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $funcionario->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $funcionario->ultimo_acesso ? $funcionario->ultimo_acesso->diffForHumans() : 'Nunca' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="editarFuncionario({{ $funcionario->id }})" 
                                            class="text-orange-600 hover:text-orange-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleStatus({{ $funcionario->id }})" 
                                            class="text-{{ $funcionario->ativo ? 'red' : 'green' }}-600 hover:text-{{ $funcionario->ativo ? 'red' : 'green' }}-900">
                                        <i class="fas fa-{{ $funcionario->ativo ? 'ban' : 'check' }}"></i>
                                    </button>
                                    @if(Auth::user()->id != $funcionario->id)
                                        <button onclick="deletarFuncionario({{ $funcionario->id }})" 
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Cadastro/Edição -->
<div id="modalFuncionario" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">
            <i class="fas fa-user-plus mr-2 text-orange-primary"></i>
            <span id="modalTitle">Novo Funcionário</span>
        </h3>
        
        <form id="formFuncionario" method="POST" action="{{ route('funcionarios.store') }}">
            @csrf
            <input type="hidden" id="funcionario_id" name="funcionario_id">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
                    <input type="text" name="name" id="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                    <input type="password" name="password" id="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                           placeholder="Mínimo 8 caracteres"
                           autocomplete="new-password">
                    <p id="passwordHint" class="mt-1 text-xs text-gray-500 hidden">Deixe em branco para manter a senha atual.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent"
                           placeholder="Repita a senha"
                           autocomplete="new-password">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cargo</label>
                    <select name="role" id="role" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-primary focus:border-transparent">
                        <option value="funcionario">Funcionário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div>
                    <label class="flex items-center">
                        {{-- hidden garante false quando desmarcado; value=1 evita envio de "on" --}}
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" id="ativo" value="1" checked
                               class="mr-2 text-orange-primary focus:ring-orange-primary">
                        <span class="text-sm font-medium text-gray-700">Usuário Ativo</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModalFuncionario()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-orange-primary text-white rounded-lg hover:bg-orange-secondary">
                    <i class="fas fa-save mr-2"></i>Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const funcionarioRoutes = {
        store: @json(route('funcionarios.store')),
        json: @json(url('/funcionarios')),
        updateBase: @json(url('/funcionarios')),
    };

    function openModalFuncionario() {
        document.getElementById('modalTitle').textContent = 'Novo Funcionário';
        document.getElementById('formFuncionario').reset();
        document.getElementById('formFuncionario').action = funcionarioRoutes.store;
        document.getElementById('formFuncionario').method = 'POST';

        let methodInput = document.getElementById('form_method');
        if (methodInput) methodInput.remove();

        document.getElementById('funcionario_id').value = '';
        document.getElementById('password').required = true;
        document.getElementById('password_confirmation').required = true;
        document.getElementById('passwordHint').classList.add('hidden');
        document.getElementById('ativo').checked = true;
        document.getElementById('modalFuncionario').classList.remove('hidden');
    }

    function closeModalFuncionario() {
        document.getElementById('modalFuncionario').classList.add('hidden');
    }

    async function editarFuncionario(id) {
        try {
            const res = await fetch(`${funcionarioRoutes.json}/${id}/json`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });

            if (!res.ok) {
                throw new Error('Não foi possível carregar os dados do funcionário.');
            }

            const data = await res.json();

            document.getElementById('modalTitle').textContent = 'Editar Funcionário';
            document.getElementById('funcionario_id').value = data.id;
            document.getElementById('name').value = data.name || '';
            document.getElementById('email').value = data.email || '';
            document.getElementById('role').value = data.role || 'funcionario';
            document.getElementById('ativo').checked = !!data.ativo;
            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';
            document.getElementById('password').required = false;
            document.getElementById('password_confirmation').required = false;
            document.getElementById('passwordHint').classList.remove('hidden');

            const form = document.getElementById('formFuncionario');
            form.action = `${funcionarioRoutes.updateBase}/${data.id}`;
            form.method = 'POST';

            let methodInput = document.getElementById('form_method');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.id = 'form_method';
                form.appendChild(methodInput);
            }
            methodInput.value = 'PUT';

            document.getElementById('modalFuncionario').classList.remove('hidden');
        } catch (e) {
            alert(e.message || 'Erro ao abrir edição.');
        }
    }

    function toggleStatus(id) {
        if (!confirm('Deseja alterar o status deste funcionário?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${funcionarioRoutes.updateBase}/${id}/toggle-status`;
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }

    function deletarFuncionario(id) {
        if (!confirm('Tem certeza que deseja excluir este funcionário?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${funcionarioRoutes.updateBase}/${id}`;
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection