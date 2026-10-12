# Credenciais de teste — Sistema de Oficina

Use estas contas **apenas em ambiente de desenvolvimento/homologação**.

Para recriar/atualizar no banco:

```bash
cd sistema-oficina
php artisan db:seed --class=ContasTesteSeeder
```

---

## Painel administrativo (`/login`)

| Perfil | E-mail | Senha | Acesso |
|--------|--------|-------|--------|
| **Administrador** | `admin@oficina.com` | `admin123` | Todos os módulos (financeiro, relatórios, agendamentos, funcionários, tema, etc.) |
| **Mecânico / Funcionário** | `mecanico@oficina.com` | `mecanico123` | OS atribuídas, checklist, atribuições, notificações (sem área admin) |

---

## Portal do cliente (`/portal`)

| Campo | Valor |
|-------|--------|
| **CPF** | `529.982.247-25` (ou só números: `52998224725`) |
| **E-mail** | `cliente.teste@oficina.com` |
| **Senha** | `cliente123` |
| **Placa (consulta pública)** | `ABC1D23` |

**Consulta sem login:** `/portal/consulta` → CPF + placa acima.

---

## Observações

- Altere as senhas antes de colocar em produção.
- O cliente de teste possui veículo **Gol — placa ABC1D23** para testar histórico e consulta.
