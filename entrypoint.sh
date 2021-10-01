#!/bin/sh

set -eu

# Set deploy key
SSH_PATH="$HOME/.ssh"
mkdir -p "$SSH_PATH"
echo "$SSH_PRIVATE_KEY" > "$SSH_PATH/deploy_key"
chmod 600 "$SSH_PATH/deploy_key"

# Do deployment
sh -c "rsync ${INPUT_RSYNC_OPTIONS} -e 'ssh -i $SSH_PATH/deploy_key -o StrictHostKeyChecking=no' ${GITHUB_WORKSPACE}${INPUT_RSYNC_SOURCE} ${SSH_USERNAME}@${SSH_HOSTNAME}:${INPUT_RSYNC_TARGET}"
