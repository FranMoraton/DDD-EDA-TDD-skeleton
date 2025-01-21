# Rapid Initialization Project

This project is designed to simplify the creation of new applications following the principles of **Domain-Driven Design (DDD)**, **Event-Driven Architecture (EDA)**, and **Test-Driven Development (TDD)**. It provides a basic structure with preconfigured tools, automated commands, and integrated best practices.

---

## Features

- **DDD-based structure**: Modular organization focused on the domain.
- **EDA support**: Built-in support for events and messaging.
- **TDD**: Initial setup for automated testing.
- **Automation**: Includes a `Makefile` to simplify common tasks.
- **Preconfigured tools**:
    - Docker for containerization.
    - Composer for dependency management.
    - GrumPHP to enforce code quality.
    - Support for migrations and message transport.
- **Authentication and Authorization Module**: Provides a basic structure for handling user authentication and access control.
- **Default Admin User**: A preconfigured admin user to simplify authentication during development.
- **Cache of Queries**: Optimized query caching using a custom implementation that extends CacheQuery, enabling efficient and reusable cache strategies for database operations.
- **Acceptance Tests**: Behat-based acceptance tests are integrated into the project to ensure that business requirements are met. Run the tests with make behat.
- **API Documentation**:
    - **AsyncAPI** available at [http://localhost:8091](http://localhost:8091).
    - **OpenAPI** available at [http://localhost:8090](http://localhost:8090).

- **Kubernetes Deployment Base**: A basic Kubernetes deployment setup is included to allow you to deploy the application to a Kubernetes cluster. It includes deployment, service, and ingress configurations to get your app running in a Kubernetes environment.
---

## Prerequisites

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Make](https://www.gnu.org/software/make/)
- Optional: Basic understanding of DDD, EDA, and TDD principles.

---

## Getting Started

### 1. Clone this repository

```bash
git clone <repository-url> cd <project-name>
```

### 2. Initialize the project

Run the `make init` command to prepare the environment:

```bash
make init
```

This command performs the following tasks:

- Cleans up existing containers and volumes.
- Builds the Docker images.
- Installs Composer dependencies.
- Runs initial migrations.

### 3. Start the services

Start the services in the background using the following command:

```bash
make start
```

### 4. Access the PHP container

If you need to interact directly with the environment, you can open a shell in the PHP container:

```bash
make bash
```

### 5. Verify everything is working correctly

- Run the tests to ensure the environment is properly set up:

```bash
make tests
```

- Check the logs if you encounter any issues:

```bash
make logs
```

### 6. Consume Commands and Events

You can use the Makefile to consume messages from the commands and events transports:

- To consume commands:

```bash
make consume-commands
```

- To consume events:

```bash
make consume-events
```

### 7. View Available Commands

To see all available commands, run:

```bash
make help
```

### 8. Acceptance Tests with Behat

This project includes acceptance tests to validate business processes and features. To run the Behat tests, use the following command:

```bash
make behat
```

Behat will execute the scenarios defined in the features directory to ensure everything behaves as expected.

### 9. Access the Application
   To access a Bearer Token for authentication,
   use the /users/v1/login endpoint with the following credentials for the default admin user:

```json
{
  "email": "test@test.com",
  "password": "Test1234$"
}
```

### 10. Access API Documentation
   AsyncAPI documentation is available at http://localhost:8091.
   OpenAPI documentation is available at http://localhost:8090.

### 11. Kubernetes Deployment (Local Minikube Setup)

To deploy the application locally in a Kubernetes cluster, you can use Minikube. Here are the steps to set it up and run the application on your local machine.

## Requirements:
- [Minikube](https://minikube.sigs.k8s.io/docs/start)
- [Kubernetes](https://kubernetes.io/)

```bash
make run-minikube
```

use minikube ip to know your local ip

```bash
minikube ip
```

## Key Principles

- **DDD**: Organizes code into clear, domain-aligned modules.
- **EDA**: Implements an asynchronous architecture with event and command transport.
- **TDD**: Encourages writing tests first to ensure robust development.

---

## Contributing

Contributions to improve this project are welcome. Please make sure to follow these guidelines:

- Use GrumPHP to validate code quality.
- Write tests for all new features.
- Add Documentation in docs/

---

Enjoy building your projects with this solid and well-structured foundation!
