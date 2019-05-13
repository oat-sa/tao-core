pipeline {
    agent any
    stages {
        stage('Initialization') {
            environment {
                GITHUB_ORGANIZATION='oat-sa'
            }
            steps {
                sh(
                    label : 'Create build build directory',
                    script: 'mkdir -p build'
                )

                withCredentials([usernamePassword(credentialsId: 'tmpaccess', usernameVariable: 'GIT_USER', passwordVariable: 'GIT_SECRET')]) {
                    sh 'printenv'
                    sh(
                        label : 'Run the Dependency Resolver',
                        script: '''
changeBranch=$CHANGE_BRANCH
branch="${changeBranch:-$BRANCH_NAME}"
echo "select branch : $branch"
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
                    script: 'cd build && composer update --no-interaction --no-ansi --no-progress'
                )
                sh(
                    label: 'Run backend tests',
                    script: 'cd build && ./vendor/bin/phpunit tao/test/unit'
                )
            }
        }
    }
}
