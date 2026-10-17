# 4 ARQUITETURA DE INFRAESTRUTURA, CONTEINERIZAÇÃO E DEPLOYMENT

## 4.1 VISÃO GERAL DA INFRAESTRUTURA DE PRODUÇÃO

Com a finalidade de conferir resiliência, reprodutibilidade, segurança e escalabilidade ao Sistema de Gerenciamento de Oficina Mecânica, concebeu-se uma arquitetura baseada no paradigma de conteinerização e integração contínua (CI/CD). A solução foi projetada para mitigar o clássico problema de discrepância de ambientes ("funciona na minha máquina, mas falha em produção"), padronizando o ecossistema de execução desde o estágio de desenvolvimento local até a publicação em nuvem em plataforma de *Platform as a Service* (PaaS).

A infraestrutura é estruturada em três pilares fundamentais:
1. **Conteinerização da Aplicação:** Construção de uma imagem única, imutável e auto-suficiente que conjuga o *runtime* PHP 8.4-FPM e o servidor web reverso Nginx sobre uma distribuição enxuta Alpine Linux.
2. **Orquestração Multi-Serviços Local:** Utilização do *Docker Compose* para gerenciar localmente o ecossistema composto pela aplicação web, o Sistema Gerenciador de Banco de Dados (SGBD) PostgreSQL 16 e o serviço de memória em cache Redis 7.
3. **Esteira de Entrega Contínua e Borda de Produção:** Hospedagem em nuvem via Railway, vinculada a repositório Git (*fork*), com provisionamento de infraestrutura gerenciada, resolução DNS avançada com *CNAME Flattening* via Cloudflare e terminação criptográfica SSL/TLS automatizada.

```
       +--------------------------------------------------------------+
       |                  CLIENTE / NAVEGADOR WEB                     |
       +--------------------------------------------------------------+
                                      |
                           HTTPS (Porta 443)
                                      v
       +--------------------------------------------------------------+
       |                 CLOUDFLARE (DNS & EDGE PROXY)                |
       |  - Resolução CNAME Flattening no domínio raiz (@) e (www)    |
       |  - Roteamento transparente para a infraestrutura PaaS        |
       +--------------------------------------------------------------+
                                      |
                           HTTPS (Proxy Reverso)
                                      v
       +--------------------------------------------------------------+
       |                      RAILWAY EDGE PROXY                      |
       |  - Terminação SSL/TLS (Let's Encrypt / ACME Challenge)       |
       |  - Injeção dinâmica da porta de borda ($PORT)                |
       +--------------------------------------------------------------+
                                      |
                     HTTP / Roteamento Interno ($PORT)
                                      v
  ==========================================================================
  CONTAINER DOCKER: SISTEMA OFICINA (PHP 8.4-FPM + NGINX ALPINE)
  --------------------------------------------------------------------------
  |  [ Nginx ]                                                             |
  |    -> Escuta dinâmica em $PORT, 80 e 8080                              |
  |    -> Entrega imediata de arquivos estáticos (/public)                 |
  |    -> Encaminhamento FastCGI com cabeçalhos X-Forwarded-*              |
  |                                                                        |
  |  [ PHP-FPM 8.4 (127.0.0.1:9000) ]                                      |
  |    -> Framework Laravel 12                                             |
  |    -> TrustProxies ativado (Reconhecimento de tráfego seguro)          |
  |    -> OPcache, PDO PgSQL e Phpredis habilitados                        |
  ==========================================================================
             |                                             |
     Conexão TCP (5432)                            Conexão TCP (6379)
             v                                             v
  +----------------------+                      +----------------------+
  |    POSTGRESQL 16     |                      |       REDIS 7        |
  | (Persistência / SGBD)|                      | (Cache / Sessão / SLA|
  +----------------------+                      +----------------------+
```
*Figura 1 – Diagrama da Arquitetura de Infraestrutura e Roteamento de Borda. Fonte: Elaborado pelo autor (2026).*

---

## 4.2 CONTEINERIZAÇÃO COM DOCKER E CONSTRUÇÃO EM MÚLTIPLOS ESTÁGIOS (*MULTI-STAGE BUILD*)

Para otimizar o tempo de *build*, reduzir drasticamente a superfície de ataque e minimizar o tamanho final da imagem em disco, adotou-se o padrão *Multi-Stage Build* no Dockerfile. Esse processo segmenta a esteira de montagem em três estágios especializados e estanques:

### 4.2.1 Estágio 1: Compilação de Ativos Frontend (*node-builder*)
O primeiro estágio utiliza a imagem `node:18-slim` com o objetivo exclusivo de preparar os recursos de interface. Os arquivos de manifesto (`package.json` e `package-lock.json`) são isolados em camada para aproveitamento de cache. Em seguida, os módulos necessários são instalados e o empacotador Vite executa a minificação e a compilação dos arquivos JavaScript e CSS para o diretório `/build/public/build`. As ferramentas do Node.js são descartadas após a conclusão, não compondo a imagem final.

### 4.2.2 Estágio 2: Resolução de Dependências Backend (*composer-builder*)
O segundo estágio emprega a imagem oficial `composer:2`. Realiza-se a instalação das dependências PHP em modo estrito de produção através do comando:
```bash
composer install --no-dev --no-interaction --no-scripts --optimize-autoloader --prefer-dist --ignore-platform-reqs
```
Nessa fase, o carregamento dinâmico (*autoloader*) é otimizado e compilado com a diretiva `--ignore-platform-reqs`, complementada pela desativação explícita de checagem de plataforma no `composer.json` (`"platform-check": false`), prevenindo bloqueios de execução gerados por divergências entre o ambiente de construção e o de execução.

### 4.2.3 Estágio 3: Imagem Definitiva de Produção (*production*)
A imagem executável final utiliza como base `php:8.4-fpm-alpine`. A seleção da variante Alpine Linux garante um consumo mínimo de recursos computacionais e memória RAM. 

Neste estágio:
- Compilam-se as extensões nativas essenciais do PHP: `pdo_pgsql`, `pgsql`, `gd`, `bcmath`, `opcache`, `zip`, `intl` e `mbstring`;
- Instala-se o cliente oficial `postgresql-client` e as bibliotecas de desenvolvimento `libpq-dev`;
- Compila-se a extensão `redis` via repositório PECL utilizando pacotes virtuais temporários (`.build-deps`), os quais são expurgados na mesma camada para preservação de espaço;
- Instala-se o servidor web Nginx no mesmo container, permitindo um encapsulamento completo do servidor de aplicação e do servidor web em uma unidade funcional coesa;
- Copiam-se unicamente os artefatos compilados dos estágios anteriores (diretórios `vendor/` e `public/build/`), além do código-fonte do sistema;
- Configuram-se as permissões de acesso do usuário de sistema `www-data` sobre os diretórios de gravação (`storage/` e `bootstrap/cache/`).

---

## 4.3 UNIFICAÇÃO DO SISTEMA GERENCIADOR DE BANCO DE DADOS (POSTGRESQL)

Durante a evolução arquitetural do projeto, homologou-se a substituição do SGBD legado (MySQL) pelo **PostgreSQL 16**. Essa alteração demandou intervenções profundas tanto na camada de infraestrutura quanto na configuração do framework:

1. **Camada de Driver PHP:** A dependência da extensão `pdo_mysql` foi descontinuada no Dockerfile em favor das extensões `pdo_pgsql` e `pgsql`, garantindo suporte nativo a tipos de dados avançados, transações atômicas robustas e alta integridade referencial.
2. **Ambiente Local e Tratamento de Conflito de Volumes:** Durante os testes locais com o Docker Compose, observou-se falha crítica de inicialização no container de banco de dados (`sistema-oficina-db-1 exited with code 1`). O diagnóstico revelou que o volume persistente nomeado (`sistema-oficina_db_data`) continha arquivos de dados residuais do motor MySQL (`ibdata1`, `mysql.sock`). A resolução exigiu o expurgo completo dos volumes legados via `docker compose down -v`, possibilitando que o utilitário `initdb` do PostgreSQL 16 estruturasse uma base íntegra e vazia.
3. **Parametrização do Framework:** O arquivo de configuração `config/database.php` foi ajustado para priorizar a variável unificada `DATABASE_URL`, permitindo que o Laravel decodifique dinamicamente *string* de conexão, credenciais, porta e catálogo, viabilizando interoperabilidade imediata com plataformas de nuvem.

---

## 4.4 CICLO DE VIDA DO CONTAINER E AUTOMAÇÃO OPERACIONAL (*ENTRYPOINT*)

Para viabilizar uma inicialização autônoma, resiliente e compatível com as premissas de sistemas nativos para a nuvem (*Cloud Native*), implementou-se um *script* customizado de ponto de entrada (`docker/entrypoint.sh`).

O script desempenha sequencialmente as seguintes operações no momento do boot do container:

1. **Adequação Dinâmica de Portas:** Avalia se a variável de ambiente `$PORT` foi injetada pela plataforma de nuvem. Caso positivo, e se divergente das portas convencionais (80 e 8080), aplica uma injeção de diretiva `listen` no arquivo `docker/nginx/default.conf` antes de carregar o serviço.
2. **Validação Estrutural do Nginx:** Executa `nginx -t` para comprovar a higidez sintática dos arquivos de configuração do servidor web.
3. **Inicialização do Nginx em Segundo Plano:** O processo do Nginx é despachado como *daemon*, iniciando a escuta imediata de conexões na borda.
4. **Criação de Vínculo de Armazenamento:** Executa `php artisan storage:link` com tratamento tolerante a falhas, assegurando o acesso público a uploads e documentos gerados.
5. **Otimização e Cache de Produção:** Executa de forma sequencial o pré-carregamento das configurações (`config:cache`), o mapa estático de rotas (`route:cache`) e a pré-compilação dos modelos visuais (`view:cache`), elevando a taxa de vazão de requisições por segundo.
6. **Migração Automatizada do Banco de Dados:** Executa `php artisan migrate --force`. Todas as 22 tabelas estruturais e evolutivas do sistema são criadas ou atualizadas de forma autônoma durante a esteira de implantação.
7. **Semeadura Idempotente de Administrador:** Executa `php artisan db:seed --class=UserSeeder --force`. Utilizando o método declarativo `User::firstOrCreate()`, o sistema garante a existência do usuário gestor inicial (`admin@oficina.com`), sem ocasionar violações de chave primária ou duplicação de registros em reinicializações posteriores.
8. **Despacho do Processo Principal:** Transfere a execução para o PHP-FPM em primeiro plano (`php-fpm -F`), garantindo o encadeamento adequado de sinais POSIX (`SIGTERM`, `SIGINT`) para encerramento gracioso (*graceful shutdown*).

---

## 4.5 ORQUESTRAÇÃO DE DESENVOLVIMENTO COM DOCKER COMPOSE

O ambiente de homologação e testes locais foi consolidado no manifesto declarativo `docker-compose.yml`. O ambiente é segregado em uma rede isolada do tipo *bridge* (`app-network`) e abrange três serviços interdependentes:

* **`app`:** Instância do container consolidado da aplicação. Mapeia a porta hospedeira `8000` para a porta interna `80` do Nginx. Possui dependência declarativa com condição de integridade (`condition: service_healthy`) em relação ao banco e ao cache.
* **`db`:** Instância do PostgreSQL 16 Alpine, configurado com volume persistente `db_data` e sonda de verificação ativa (*healthcheck*) executando periodicamente `pg_isready -U postgres -d sistema_oficina`.
* **`redis`:** Instância do Redis 7 Alpine, associada ao volume `redis_data`, com sonda periódica de integridade através do comando `redis-cli ping`.

Essa configuração assegura que a aplicação Laravel só inicie seu *bootstrap* após a comprovação inconteste de que os serviços de dados e memória volátil estão aptos a aceitar conexões TCP.

---

## 4.6 PIPELINE DE INTEGRAÇÃO E DEPLOYMENT CONTÍNUO (CI/CD) NO RAILWAY

A publicação em escala do sistema foi viabilizada mediante a plataforma PaaS Railway, estruturada a partir de um modelo de ramificação (*fork*) mantido no GitHub (`MatheusHMMachado/sistema-oficina`).

### 4.6.1 Configuração Declarativa do Orquestrador
Adicionou-se ao repositório o arquivo de governança de implantação `railway.toml`, explicitando as diretrizes de compilação e verificação de saúde da instância:
```toml
[build]
builder = "dockerfile"
dockerfilePath = "Dockerfile"

[deploy]
restartPolicyType = "on-failure"
restartPolicyMaxRetries = 3
healthcheckPath = "/health"
healthcheckTimeout = 100
```

### 4.6.2 Resolução da Sonda de Integridade (*Healthcheck*)
Originalmente, a sondagem de integridade apontava para a rota raiz (`/`). No ecossistema Laravel, o acesso desautenticado a `/` emite um cabeçalho de redirecionamento `HTTP 302 Found` apontando para `/login`. Por padrão, o balanceador do Railway requer estritamente códigos da classe `2xx` para validar a inicialização. A divergência causava falso positivo de esgotamento de tempo (*healthcheck timeout*).

Para sanar a incompatibilidade, implementou-se em `routes/web.php` uma rota pública e leve:
```php
Route::get('/health', function () {
    return response('OK', 200)->header('Content-Type', 'text/plain');
});
```
O *script* de saúde interno (`docker/healthcheck.sh`) e o orquestrador da nuvem foram repontados para `/health`, garantindo estabilidade imediata na esteira de *rolling update*.

### 4.6.3 Provisionamento e Variáveis de Referência
Os bancos de dados PostgreSQL e Redis de produção foram instanciados como serviços gerenciados no mesmo projeto. A comunicação entre o serviço web e as bases de dados foi parametrizada por **Variáveis de Referência** do Railway:
- `DATABASE_URL`: `${{Postgres.DATABASE_URL}}`
- `REDIS_URL`: `${{Redis.REDIS_URL}}`

O uso de referências dinâmicas desacopla as credenciais do código-fonte e garante que quaisquer rotações de credenciais ou manutenções nos servidores de banco sejam propagadas de forma instantânea para a aplicação.

---

## 4.7 RESOLUÇÃO DE PROBLEMAS DE REDE, PROXIES E ATIVOS ESTÁTICOS

Ao longo da transição para a nuvem, três comportamentos anômalos críticos foram identificados, diagnosticados e solucionados:

### 4.7.1 Falha 502 Bad Gateway (*Connection Refused*)
Após a primeira implantação, as requisições direcionadas ao domínio público resultavam em erro `HTTP 502 Bad Gateway` com o registro de log `"error": "connection refused"`.

* **Causa Raiz:** O Dockerfile original expunha conjuntamente as portas `80` e `9000` (`EXPOSE 80 9000`). O orquestrador da nuvem associou automaticamente a porta alvo (*Target Port*) do roteador de borda à porta `9000`. Como a porta 9000 pertence ao processo FastCGI do PHP-FPM (que não processa protocolo HTTP bruto e operava vinculado ao *loopback* `127.0.0.1`), a conexão pública era imediatamente rejeitada. Concomitantemente, o Railway injetava a variável `PORT=8080`, enquanto o Nginx aguardava conexões na porta 80.
* **Correção Definitiva:** Expurgou-se a porta `9000` do `EXPOSE` do Dockerfile, mantendo exclusivamente `EXPOSE 80 8080`. O arquivo `docker/nginx/default.conf` foi reconfigurado para escutar **simultaneamente** nas portas 80 e 8080 (IPv4 e IPv6):
  ```nginx
  server {
      listen 80 default_server;
      listen [::]:80 default_server;
      listen 8080 default_server;
      listen [::]:8080 default_server;
      ...
  ```
  Adicionalmente, os registros de acesso e erro do Nginx foram redirecionados para `/dev/stdout` e `/dev/stderr`, conferindo observabilidade total em tempo real no console de monitoramento.

### 4.7.2 Bloqueio de Conteúdo Misto (*Mixed Content*) em Produção
Com a resolução do roteamento, o sistema tornou-se acessível pela URL de produção, todavia a interface era renderizada sem nenhuma folha de estilo ou formatação visual.

* **Causa Raiz:** O tráfego do usuário chegava criptografado via HTTPS (porta 443) no balanceador de borda da nuvem, mas era entregue internamente ao container via HTTP desprotegido (porta 8080). Como o framework Laravel desconhecia que operava atrás de um proxy reverso com terminação SSL, a função auxiliar `asset('vendor/tailwindcss.js')` gerava URLs com o protocolo `http://`. Os navegadores modernos bloqueiam terminantemente o carregamento de *scripts* HTTP inseridos em documentos servidos via HTTPS (*Mixed Content Blocking*).
* **Correção Definitiva:** 
  1. No arquivo `bootstrap/app.php`, habilitou-se o método `$middleware->trustProxies(at: '*');`, instruindo o framework a confiar nos cabeçalhos `X-Forwarded-*`.
  2. No Nginx, configurou-se o repasse explícito dos parâmetros `HTTP_X_FORWARDED_PROTO`, `HTTP_X_FORWARDED_FOR` e `HTTP_X_FORWARDED_PORT` para o socket FastCGI.
  3. No arquivo `resources/views/partials/head-assets.blade.php`, alterou-se a referência do *script* estático para o caminho relativo à raiz (`/vendor/tailwindcss.js`), eliminando qualquer dependência de schema ou prefixo de domínio rígido.

### 4.7.3 Incompatibilidade Cruzada com Ambiente Local (*localhost*)
Ao aplicar forçamento irrestrito de HTTPS para sanar o problema de produção, o ambiente local de desenvolvimento deixou de carregar os estilos, exibindo erro de protocolo SSL no navegador.

* **Causa Raiz:** A rotina `URL::forceScheme('https')` havia sido vinculada genericamente à checagem `app()->environment('production')`. Como o ambiente local executava com a variável `APP_ENV=production`, o container local passou a requisitar ativos via `https://localhost:8000`. Como não existiam certificados TLS locais configurados no Docker, a requisição falhava.
* **Correção Definitiva:** Refinou-se a lógica no `app/Providers/AppServiceProvider.php`, restringindo o forçamento de esquema apenas quando houver evidência real de transporte seguro:
  ```php
  if (str_starts_with((string) config('app.url'), 'https://') || request()->header('x-forwarded-proto') === 'https' || request()->isSecure()) {
      URL::forceScheme('https');
  }
  ```
  Simultaneamente, o arquivo `docker-compose.yml` teve suas variáveis locais reajustadas para `APP_ENV=local` e `APP_DEBUG=true`, restaurando a coexistência pacífica entre desenvolvimento e produção.

---

## 4.8 GESTÃO DE DOMÍNIO CUSTOMIZADO E RESOLUÇÃO DNS (HOSTINGER E CLOUDFLARE)

A etapa final de publicação consistiu no apontamento de um domínio registrado junto à registradora Hostinger para a aplicação em nuvem.

### 4.8.1 O Desafio Técnico do Domínio Raiz e Let's Encrypt
Ao cadastrar o domínio personalizado no Railway, a autoridade certificadora (Let's Encrypt) permaneceu em estado de bloqueio com a mensagem:
> *"Certificate authority is validating challenges."*

O diagnóstico estabeleceu as seguintes causas:
1. **Restrição de CNAME no Ápice (Norma RFC 1034/1035):** O Railway exige um apontamento CNAME para rotear o tráfego dinâmico de seus *clusters*. No entanto, os padrões fundamentais da Internet e o painel nativo da Hostinger proíbem a criação de registros CNAME no domínio raiz (`@` ou `seudominio.com`). 
2. **Conflito de Registros Pré-Existentes:** A Hostinger provisiona, por padrão, registros do tipo `A` e `AAAA` apontando para páginas de estacionamento da própria empresa. Ao tentar validar a posse do domínio via desafio HTTP-01/DNS-01, os servidores da autoridade certificadora atingiam a infraestrutura da Hostinger em vez do Railway, inviabilizando a emissão da chave criptográfica.
3. **Bloqueio de Redirecionamento Interno:** A tentativa de contornar a limitação redirecionando o domínio raiz para o subdomínio `www` via painel da Hostinger foi rejeitada pelo sistema operacional da registradora sob a alegação de auto-referência (*"Não é possível redirecionar seu domínio para ele mesmo"*).

### 4.8.2 Solução Implementada via Cloudflare (*CNAME Flattening*)
Para suplantar as limitações estruturais da registradora sem onerar o projeto com custos adicionais, delegou-se a zona de DNS autoritativa para o **Cloudflare**:

1. **Delegação de Servidores de Nomes:** Os servidores de nomes padrão da Hostinger foram substituídos pelos *nameservers* atribuídos pelo Cloudflare.
2. **Higienização de Registros Conflitantes:** Todos os registros legados do tipo `A` e `AAAA` vinculados ao host `@` foram revogados da tabela DNS.
3. **Aplicação de CNAME Flattening:** Criou-se um registro CNAME para o host `@` e outro para o subdomínio `www`, ambos apontando para o alvo canônico do Railway. O recurso de *CNAME Flattening* do Cloudflare sintetiza respostas simulando registros `A` na raiz, respeitando a norma RFC e viabilizando o CNAME no ápice.
4. **Validação do Certificado com Nuvem Cinza (*DNS Only*):** Durante a etapa inicial de emissão do certificado pelo Let's Encrypt, o proxy do Cloudflare foi configurado no modo *DNS Only* (desativação temporária do proxy reverso intermediário). Isso permitiu que a autoridade certificadora negociasse o desafio ACME diretamente com o container da aplicação no Railway.
5. **Atualização da Variável Canônica:** Após o status do domínio transicionar para **Active** com a emissão do certificado TLS v1.3, atualizou-se no painel do Railway a variável de ambiente:
   ```env
   APP_URL=https://www.seudominio.com
   ```

---

## 4.9 SÍNTESE DOS COMPONENTES TECNOLÓGICOS DA INFRAESTRUTURA

A Tabela 1 sintetiza as ferramentas, versões e atribuições funcionais que consolidam a infraestrutura desenvolvida.

*Tabela 1 – Matriz de Componentes de Infraestrutura e Implantação.*

| Componente | Versão / Tecnologia | Finalidade no Projeto |
| :--- | :--- | :--- |
| **Linguagem / Runtime** | PHP 8.4.26 (FPM) | Execução central da lógica de negócios e APIs do Laravel 12. |
| **Sistema Operacional** | Alpine Linux 3.20 | Distribuição mínima e segura para compor a imagem base do container. |
| **Servidor Web** | Nginx 1.30.4 | Servidor web reverso, terminação interna e entrega de ativos estáticos. |
| **SGBD Principal** | PostgreSQL 16 Alpine | Armazenamento persistente de dados transacionais com alta integridade. |
| **Armazenamento em Cache**| Redis 7 Alpine | Gerenciamento volátil de sessões, cache de aplicação e filas de serviço. |
| **Frontend Bundler** | Node.js 18 / Vite 4.5 | Compilação e minificação de bibliotecas e ativos de interface. |
| **Conteinerização** | Docker (Multi-stage) | Padronização e isolamento da unidade de software em camadas imutáveis. |
| **Orquestração Local** | Docker Compose v2 | Composição e inicialização simultânea dos serviços de desenvolvimento. |
| **Hospedagem em Nuvem** | Railway (PaaS) | Execução contínua em nuvem com *zero-downtime* e monitoramento. |
| **Gestão DNS / Borda** | Cloudflare | *CNAME Flattening*, resolução de domínios raiz e mitigação de latência. |
| **Certificação SSL/TLS** | Let's Encrypt (ACME) | Criptografia ponta a ponta (HTTPS) com renovação automatizada. |

*Fonte: Elaborado pelo autor (2026).*
