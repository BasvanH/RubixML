# Contributing Guidelines
Thank you for considering a code contribution to Rubix ML. We strongly believe that our contributors play the most important role in bringing powerful machine learning tools to the PHP language. Please read over the following guidelines so that we can continue to provide high quality machine learning tools that our users love.

### Pull Request Checklist
Here are a few things to check off before sending in a pull request ...

- The change provides high value to Rubix ML engineers
- The change does not introduce unnecessary complexity
- Your changes are consistent with our [coding style](#coding-style)
- Your changes pass [static analysis](#static-analysis)
- All [unit tests](#unit-testing) pass
- Does your change require updates to the documentation?
- Does an entry to the CHANGELOG need to be added?

> New to pull requests? Github has a great [howto](https://help.github.com/articles/about-pull-requests/) to get you started.

### Coding Style
Rubix ML follows the PSR-2 coding style with additional rules. For any new class, include a class header that contains a title, a short description of what the class does, any references, and the author and package name. If you are making changes to an existing class, you may add your name to the author list under the last entry if you want.

To run the style checker:
```sh
$ composer check
```

To run the style fixer:
```sh
$ composer fix
```

### Static Analysis
Static code analysis is an integral part of our overall testing and quality assurance strategy. Static analysis allows us to catch bugs before they make it into the codebase. Therefore, it is important that your updates pass static analysis at the level set by the project lead.

To run static analysis:
```sh
$ composer analyze
```
  
### Unit Testing
All new code *requires* an accompanying unit test whether it be a new feature or a bug fix. What to test depends on the type of change you are making. See the individual unit testing guidelines below.

To run the unit tests:
```sh
$ composer test
```

#### Class Organization
In Rubix ML, class definitions flow in the following manner from top to bottom - traits, class constants, properties, static methods, constructor, methods.

#### Learner Testing Guidelines
Rubix ML uses a unique end-to-end testing schema for all learners that involves generating a controlled training and testing set, training the learner, and then validating its predictions using an industry-standard scoring metric. The reason for this type of test is to be able to confirm that the learner offers the ability to generalize its training to the real world. Since not all learners offer the same performance, choose a generator and minimum validation score that is appropriate for a real world use case.

> **Note:** Be sure to seed the random number generator with a known constant value in your tests.

#### Bugfix Testing Guidelines
Typically bugs indicate an area of the code that has not been properly tested yet. When submitting a bug fix, please include a passing test that would have reproduced the bug prior to your changes.

### Mutability Policy
Objects implemented in Rubix ML have a mutability policy of *generally* immutable which means properties are kept protected and state cannot be modified without creating a new object. Certain objects such as Learners have model parameters that are mutated during training. In such cases, mutability must be controlled through public interfaces. In general, any stateful object that requires mutation must only be updated through a well-defined public method. In some special cases, such as for performance reasons, object properties may be allowed to be mutated directly.

### Anti Plagiarism Policy
Our community takes a strong stance against plagiarism, or the copying of another author's code without attribution. Since the spirit of open source is to make code freely available, it is up to the community to enforce policies that deter plagiarism. As such, we do not allow contributions from those who violate this policy.