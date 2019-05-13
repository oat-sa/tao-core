pipeline {
    agent any
    stages {
        stage('Initialization') {
            environment {
                GITHUB_ORGANIZATION='oat-sa'
                GITHUB_REPO='oat-sa/tao-core'
            }
            steps {
                sh(
                    label : 'Create build build directory',
                    script: 'mkdir -p build'
                )

                withCredentials([usernamePassword(credentialsId: 'tmpaccess', passwordVariable: 'GIT_SECRET')]) {
                    sh(
                        label : 'Run the Dependency Resolver',
                        script: '''
changeBranch=$CHANGE_BRANCH
branch="${changeBranch:-$BRANCH_NAME}"
docker run --rm  registry.service.consul:4444/tao/dependency-resolver oat:dependencies:resolve --main-branch ${branch} --repository-name oat-sa/tao-core > build/composer.json
                        '''
                    )
                }
            }
        }
        stage('Tests') {
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
                    sh(
                        label: 'Install/Update sources from Composer',
                        script: '''
                        cd build
                        composer update --no-interaction --no-ansi --no-progress
                        '''
                    )
                    sh(
                        label: 'Run backend tests',
                        script: './vendor/bin/phpunit tao/test/unit'
                    )
                }

            }
    }
}
