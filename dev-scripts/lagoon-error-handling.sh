#!/bin/bash

error_handler() {
  DEPLOYMENT_STATUS="failure"
  GH_DEPLOYMENT_ID=$(cat /tmp/gh_deployment_id)
  echo "Setting GH deployment status '$GH_DEPLOYMENT_ID': '$DEPLOYMENT_STATUS'"

  curl -L \
    -X POST \
    -H "Authorization: Bearer $GH_DEPLOYMENT_TOKEN" \
    https://api.github.com/repos/danskernesdigitalebibliotek/dpl-cms/deployments/"$GH_DEPLOYMENT_ID"/statuses \
    -d "{\"environment\":\"$LAGOON_PR_HEAD_BRANCH\",\"state\":\"$DEPLOYMENT_STATUS\"}"

  exit "$1"
}

# Set up trap for ERR signal
trap 'error_handler $?' ERR
