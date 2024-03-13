<?php $userPilotData = get_data('userpilot_data'); ?>
<script> window.userpilotSettings = {token: "<?= $userPilotData['token'] ?>"}; </script>
<script src="https://js.userpilot.io/sdk/latest.js"></script>
<script>
    userpilot.identify(
        "<?= $userPilotData['user']['id'] ?>",
        {
            name: "<?= $userPilotData['user']['name'] ?>",
            login: "<?= $userPilotData['user']['login'] ?>",
            email: "<?= $userPilotData['user']['email'] ?>",
            interfaceLanguage: "<?= $userPilotData['user']['interface_language'] ?>",
            company: {
                id: "<?= $userPilotData['tenant']['id'] ?>",
            }
        }
    );
</script>
