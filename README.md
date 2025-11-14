# qscmf-http-tracer

QSCMF HTTP 客户端请求追踪日志包，用于自动记录 HTTP 请求和响应的完整信息。

## 功能特性

- ✅ 自动记录 HTTP 请求和响应信息
- ✅ 支持文件存储和数据库存储
- ✅ 基于 Guzzle HTTP 客户端中间件
- ✅ 记录请求耗时和完整数据

## 安装

```bash
composer require quansitech/qscmf-http-tracer
```

## 配置

### 环境变量设置

在 `.env` 文件中添加：

```env
# 存储接口请求日志的数据表名，如 api_requests_log，必填
QSCMF_HTTP_TRACE_LOGGER_TABLE_NAME=api_requests_log
```

### 数据库迁移

包会自动注册数据库迁移文件，运行迁移命令：

```bash
php artisan migrate
```

## 快速开始

### 使用数据库存储

```php
use Qscmf\HttpTracer\Client\GuzzleClient;
use Qscmf\HttpTracer\Lib\LogWriter\Context;

// 创建日志记录器（数据库存储）
$writer = Context::buildWriter('db');
$logger = new RequestLogger($writer);

// 创建带追踪的 HTTP 客户端
$client = (new GuzzleClient($logger))->create([]);

// 发送 GET 请求
$response = $client->request('GET', 'https://api.example.com/users');
```

### 使用文件存储

```php
use Qscmf\HttpTracer\Client\GuzzleClient;
use Qscmf\HttpTracer\Lib\LogWriter\Context;

// 创建日志记录器（文件存储）
$writer = Context::buildWriter('file');
$logger = new RequestLogger($writer);

// 创建带追踪的 HTTP 客户端
$client = (new GuzzleClient($logger))->create([]);

// 发送请求
$response = $client->request('GET', 'https://api.example.com/users');
```


## 数据库表结构

包会自动创建 `api_requests_log` 表（表名可配置），包含以下字段：

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | bigint | 主键 ID |
| trace_id | varchar(32) | 唯一追踪 ID |
| method | varchar(10) | HTTP 方法 |
| url | text | 请求 URL |
| request_headers | text | 请求头（JSON） |
| request_body | text | 请求体 |
| response_status_code | int | 响应状态码 |
| response_headers | text | 响应头（JSON） |
| response_body | text | 响应体 |
| duration_ms | float | 请求耗时（毫秒） |
| create_date | timestamp | 创建时间 |
| update_date | timestamp | 更新时间 |

## 日志记录内容

每个 HTTP 请求都会记录：

- **请求阶段**：方法、URL、请求头、请求体、时间戳
- **响应阶段**：状态码、响应头、响应体、请求耗时
- **关联信息**：通过 `trace_id` 关联请求和响应

## 文件存储轮转规则

使用文件存储时，Monolog RotatingFileHandler 的轮转规则：

- **轮转周期**：按天轮转
- **最大文件数**：无限制（默认值 0）
- **日志路径**：`vendor/quansitech/qscmf-http-tracer/src/logs/`

**注意**：当前配置会无限期保留所有历史日志文件，需要定期手动清理。
