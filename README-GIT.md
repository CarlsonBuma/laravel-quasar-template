# GIT Template Stream Setup
## Master Branch: <setup>
- Only SECURITY AND FRAMEWORK RELATED
- Including UserAuth by Laravel Passport: Oauth 2.0
- ONLY GIT DOWNSTREAM (Upstreams are disabled):
- ONLY Security Major Changes Bypassed

## Current Branch: <newBranchName>
- This is current Project Head (newBranchName)
- Initialization:
   - git clone <Repo>
   - git branch <newBranchName> <branchToPullFrom>    // Creating new Branch
   - git stash                                        // Stash changes form Head first
   - git checkout <newBranchName>                     // Status in new Branch
   - git add .
   - git commit -m "Description - v.1.0.0"
   - git push --set-upstream origin <newBranchname>
 - Major Changes DOWNSTREAM from Master
   - git fetch origin
   - git merge origin <branchName>
   