pipeline {
    agent {
        label 'builder'
    }
    parameters {
        string(name: 'branch', defaultValue: '')
    }
    environment {
        REPO_NAME='oat-sa/tao-core'
        EXT_NAME='tao'
        GITHUB_ORGANIZATION='oat-sa'
    }
    stages {
        stage('Prepare') {
            steps {
                sh(
                    label : 'Create build directory',
                    script: 'mkdir -p build'
                )
            }
        }
        stage('Install') {
            agent {
                docker {
                    image 'alexwijn/docker-git-php-composer'
                    reuseNode true
                }
            }
            environment {
                HOME = '.'
            }
            options {
                skipDefaultCheckout()
            }
            steps {
                dir('build') {
                    script {
                        def branch
                        if (env.CHANGE_BRANCH != null) {
                            branch = CHANGE_BRANCH
                        } else {
                            branch = BRANCH_NAME
                        }
                        env.branch = branch
                        writeFile(file: 'composer.json', text: """
                        {
                            "require": {
                                "oat-sa/extension-tao-devtools" : "dev-develop",
                                "${REPO_NAME}" : "dev-${branch}#${GIT_COMMIT}"
                            },
                            "minimum-stability": "dev",
                            "require-dev": {
                                "phpunit/phpunit": "~8.5"
                            }
                        }
                        """
                       )
                    }
                    withCredentials([string(credentialsId: 'jenkins_github_token', variable: 'GIT_TOKEN')]) {
                        sh(
                            label: 'Install/Update sources from Composer',
                            script: "COMPOSER_AUTH='{\"github-oauth\": {\"github.com\": \"$GIT_TOKEN\"}}\' composer update --no-interaction --no-ansi --no-progress --prefer-source"
                        )
                    }
                }
            }
        }
        stage('Tests') {
            parallel {
                stage('Backend Tests') {
                    agent {
                        docker {
                            image 'alexwijn/docker-git-php-composer'
                            reuseNode true
                        }
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        dir('build'){
                            sh(
                                label: 'Run backend tests',
                                script: './vendor/bin/phpunit tao/test/unit'
                            )
                        }
                    }
                }
                stage('Frontend Tests') {
                    agent {
                        docker {
                            image 'btamas/puppeteer-git'
                            reuseNode true
                        }
                    }
                    environment {
                        HOME = '.'
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        dir('build/tao/views'){
                            sh(
                                label: 'Ensure FE resource are available',
                                script: 'npm install --production'
                            )
                        }
                        dir('build/tao/views/build') {
                            sh(
                                label: 'Setup frontend toolchain',
                                script: 'npm install'
                            )
                            sh (
                                label : 'Run frontend tests',
                                script: 'npx grunt connect:test taotest'
                            )
                        }
                    }
                }
            }
        }
        stage('Checks') {
            parallel {
                stage('Backend Checks') {
                    agent {
                        docker {
                            image 'alexwijn/docker-git-php-composer'
                            reuseNode true
                        }
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        dir('build'){
                            script {
                                deps = sh(returnStdout: true, script: "php ./taoDevTools/scripts/depsInfo.php ${EXT_NAME}").trim()
                                echo deps
                                def propsJson = readJSON text: deps
                                missedDeps = propsJson[EXT_NAME]['missedClasses'].toString()
                                try {
                                    assert missedDeps == "[]"
                                } catch(Throwable t) {
                                    error("Missed dependencies found: $missedDeps")
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
