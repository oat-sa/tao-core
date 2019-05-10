pipeline {
    agent any
    stages {
        stage('Initialization') {
            steps {
                def scmUrl = scm.getUserRemoteConfigs()[0].getUrl()
                echo scmUrl
                sh(
                    label : 'Create build build directory',
                    script: 'mkdir -p build'
                )
                sh '''
docker run --rm  \\
-e "GITHUB_ORGANIZATION=oat-sa" \\
-e "GITHUB_SECRET=${gitHubToken}"  \\
registry.service.consul:4444/tao/dependency-resolver oat:dependencies:resolve --main-branch $BRANCH_NAME --repository-name tao-core > build/composer.json
                '''
                sh ''' 
    cat build/composer.json
                '''
            }
        }
    }
}
