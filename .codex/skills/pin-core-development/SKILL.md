---
name: pin-core-development
description: 在 Pin 基础仓库中开发或修改核心框架能力、公共 API 和扩展点；优先遵循 Pin 文档与源码约定，并保持与 Laravel 的边界清晰。
metadata:
  short-description: 开发 Pin 核心框架能力
---

# Pin 核心开发

这个 Skill 只服务于 [`ipiner/pin`](https://github.com/ipiner/pin) 基础仓库。它关注公共框架能力，不把 `admin` 的业务实现、`App\\*` 命名空间或某个具体应用的规则带入 Pin。

## 信息来源和优先级

实现前只读取与任务相关的资料，优先级如下：

1. 当前仓库 `pin/src` 的真实实现、测试和 PHPDoc。
2. `https://github.com/ipiner/docs` 中对应的设计和使用文档。
3. Laravel 的官方扩展点和当前 `composer.json` 允许的版本。

源码签名和现有测试优先于文档示例。若两者不一致，先确认这是实现缺陷、文档过期还是有意变更，再决定是否同时修正文档仓库。

## 组件决策

- 模型行为、事件、缓存、元数据、查询能力放在 `Pin\\Models\\Model` 或对应 Model Concern。
- 跨模型的完整写入流程使用 `Pin\\Services\\ModelService` 和其生命周期钩子；不要在核心层重复实现事务、结果对象或版本校验。
- 一次独立业务动作使用 `Pin\\Action\\Action`，保持验证、上下文和 Queryable 能力可复用。
- 查询参数使用 `Queryable`、`QueryableType` 和 `QueryableRules`；验证规则应能表达公开的查询语义。
- API 响应使用 `Pin\\Http\\ApiResponse` 和基础控制器的 `success()`/`error()`。
- 路由能力保持在 `Pin\\Route`、Attribute 和 Module 推导系统中；新增规则必须覆盖命名、推导和回退行为。
- 错误码、异常、服务结果和测试辅助类应保持统一结构，不为单个应用添加特殊分支。

## 开发边界

- Pin 是可被多个应用和包依赖的基础层。公共类要有明确的输入、返回类型和 PHPDoc，避免依赖应用配置或 `App\\*` 类。
- Laravel 原生能力可以作为底层实现或扩展点，但先确认 Pin 没有已经承诺的封装。不要为已有 Pin API 再造一层平行 API。
- 破坏公共 API、默认推导、响应结构或错误码时，先搜索调用方和测试，并说明兼容性影响。
- 文档维护在独立的 [`ipiner/docs`](https://github.com/ipiner/docs) 仓库。改变公开行为时，检查对应文档是否需要同步；除非任务明确包含文档提交，不要直接修改另一个仓库。

## 验证方式

- 为核心行为补充 `pin/tests` 中的定向测试，优先覆盖公开 API、边界条件和失败路径。
- 运行 `pin` 仓库自己的 Pest/PHPUnit、Pint 和 PHPStan 检查，命令以当前 `composer.json` 与配置文件为准。
- 修改路由、模块推导、Action、ModelService、Queryable 或响应时，检查相关文档章节和现有测试，不要只验证单个 happy path。
- 完成说明中报告：改变的公共能力、对应文档章节、测试命令，以及是否需要 Admin 或其他依赖方配套修改。

## 相关文档

按需阅读 [references/pin-docs-map.md](references/pin-docs-map.md)，不要一次性加载整个文档站点。
