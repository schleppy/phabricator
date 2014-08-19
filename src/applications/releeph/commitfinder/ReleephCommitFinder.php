<?php

final class ReleephCommitFinder {

  private $releephProject;
  private $user;
  private $objectPHID;

  public function setUser(PhabricatorUser $user) {
    $this->user = $user;
    return $this;
  }
  public function getUser() {
    return $this->user;
  }

  public function setReleephProject(ReleephProject $rp) {
    $this->releephProject = $rp;
    return $this;
  }

  public function getRequestedObjectPHID() {
    return $this->objectPHID;
  }

  public function fromPartial($partial_string) {
    $this->objectPHID = null;

    // Look for diffs
    $matches = array();
    if (preg_match('/^D([1-9]\d*)$/', $partial_string, $matches)) {
      $diff_id = $matches[1];
      // TOOD: (T603) This is all slated for annihilation.
      $diff_rev = id(new DifferentialRevision())->load($diff_id);
      if (!$diff_rev) {
        throw new ReleephCommitFinderException(
          "{$partial_string} does not refer to an existing diff.");
      }
      $commit_phids = $diff_rev->loadCommitPHIDs();

      if (!$commit_phids) {
        throw new ReleephCommitFinderException(
          "{$partial_string} has no commits associated with it yet.");
      }

      $this->objectPHID = $diff_rev->getPHID();

      $commits = id(new PhabricatorRepositoryCommit())->loadAllWhere(
        'phid IN (%Ls) ORDER BY epoch ASC',
        $commit_phids);
      return head($commits);
    }

    // Look for a raw commit number, or r<callsign><commit-number>.
    $repository = $this->releephProject->getRepository();
    $dr_data = null;
    $matches = array();
    if (preg_match('/^r(?P<callsign>[A-Z]+)(?P<commit>\w+)$/',
      $partial_string, $matches)) {
      $callsign = $matches['callsign'];
      if ($callsign != $repository->getCallsign()) {
        throw new ReleephCommitFinderException(sprintf(
          '%s is in a different repository to this Releeph project (%s).',
          $partial_string,
          $repository->getCallsign()));
      } else {
        $dr_data = $matches;
      }
    } else {
      $dr_data = array(
        'callsign' => $repository->getCallsign(),
        'commit' => $partial_string
      );
    }

    try {
      $dr_data['user'] = $this->getUser();
      $dr = DiffusionRequest::newFromDictionary($dr_data);
    } catch (Exception $ex) {
      $message = "No commit matches {$partial_string}: ".$ex->getMessage();
      throw new ReleephCommitFinderException($message);
    }

    $phabricator_repository_commit = $dr->loadCommit();

    if (!$phabricator_repository_commit) {
      throw new ReleephCommitFinderException(
        "The commit {$partial_string} doesn't exist in this repository.");
    }

    // When requesting a single commit, if it has an associated review we
    // imply the review was requested instead. This is always correct for now
    // and consistent with the older behavior, although it might not be the
    // right rule in the future.
    $phids = PhabricatorEdgeQuery::loadDestinationPHIDs(
      $phabricator_repository_commit->getPHID(),
      PhabricatorEdgeConfig::TYPE_COMMIT_HAS_DREV);
    if ($phids) {
      $this->objectPHID = head($phids);
    }

    return $phabricator_repository_commit;
  }

}
