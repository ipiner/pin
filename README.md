# Pin

Pin 是一个面向 Laravel API 应用的轻量级开发基座。

Pin 建立在 Laravel 原生能力之上，通过统一的工程约定，将路由定义、业务流程、查询能力、响应规范、错误处理、自动化测试与 API 文档贯穿起来，形成从设计、实现到验证的完整开发闭环。

## 核心特性

- [配置分层](https://ipiner.cn/guide/configuration)：按顺序加载并递归合并配置，灵活覆盖所需配置项。
- [路由枚举](https://ipiner.cn/features/routing)：使用枚举定义 API 路由，并贯穿路由注册、URL 生成与接口测试。
- [Action](https://ipiner.cn/features/action)：组织业务流程，使业务逻辑保持清晰、独立且易于测试。
- [查询对象](https://ipiner.cn/model/queryable)：基于验证规则声明查询能力，并转换为 Eloquent 查询条件。
- [错误码](https://ipiner.cn/features/errors)：使用枚举定义错误码，统一 API 错误规范。
- [Fake 数据](https://ipiner.cn/testing/fake)：基于验证规则生成模拟数据，快速构建接口测试场景。
- [HTTP 测试](https://ipiner.cn/testing/http-tests)：基于路由枚举构建测试请求，保持测试代码与路由定义一致。
<!-- - [API 文档生成](https://ipiner.cn/digging-deeper/scramble)：根据代码结构和类型生成接口文档，保持文档与代码同步。 -->

## 文档

[https://ipiner.cn](https://ipiner.cn)
