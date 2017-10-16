<?php
try {
  $data = array(
    'name' => 'Candidate Test One',
    'individual' => array(
      'indiv_name' => array(
         'given' => 'Candidate',
         'middle' => 'Test',
         'family' => 'One',
      ),
      'indiv_phone' => '07837 551 763',
      'indiv_dob' => '1990-02-28',
      'indiv_location' => array(
        'title' => 'Home Address',
        'type' => 'address',
        'address' => array(
          'country_code' => 'GB',
          'address_line1' => '4 Dale Street',
          'locality' => 'Manchester',
          'postal_code' => 'M22 7HJ',
        ),
      ),
    ),
    'education' => array(
      array(
        'education_from' => '2008-09-01',
        'education_to' => '2011-06-01',
        'education_institution' => 'University of Manchester',
        'education_qualifications' => array(
          array(
            'qualification_grade' => '1st',
            'qualification_level' => 'b_degree',
            'qualification_title' => 'Computer Science and Mathematics',
          ),
        ),
      ),
      array(
        'education_from' => '2001-09-01',
        'education_to' => '2008-06-01',
        'education_institution' => 'Richard Challoner School',
        'education_qualifications' => array(
          array(
            'qualification_grade' => 'A',
            'qualification_level' => 'gcse',
            'qualification_title' => 'Mathematics',
          ),
          array(
            'qualification_grade' => 'A',
            'qualification_level' => 'gcse',
            'qualification_title' => 'English Literature',
          ),
        ),
      ),
    ),
    'experience_work' => array(
      array(
        'workexp_company' => 'Websites Inc.',
        'workexp_current' => FALSE,
        'workexp_start' => '2011-11-20',
        'workexp_end' => '2013-09-01',
        'workexp_job_title' => 'Junior Developer',
        'workexp_location' => array(
          'type' => 'address',
          'address' => 'country_code',
          'address_line1' => '123 A Street',
          'locality' => 'A Town',
          'postal_code' => 'M1 5JG',
        ),
        'workexp_projects' => array(
          array(
            'proj_company' => 'Twitter Inc.',
            'proj_description' => 'Working on a plugin for Twitter',
            'proj_from' => '2011-11-20',
            'proj_to' => '2012-02-21',
            'proj_name' => 'Random Twitter Plugin',
            'proj_responsibilities' => array(
              array(
                'resp_responsibility' => 'Scripting PHP',
                'resp_description' => 'Wrote High Quality PHP Code to make the plugin work',
              ),
              array(
                'resp_responsibility' => 'Code QA',
                'resp_description' => 'Tested and fixed code and was responsible for quality assurance',
              ),
            ),
            'proj_skills' => array(
              array(
                'skill_skill' => 'PHP',
                'skill_level' => 'expert',
              ),
            ),
          ),
        ),
      ),
    ),
  );
  $service = \Drupal::service('sowlo.candidate_importer');
  $candidate = $service->importSingle(json_decode(json_encode($data)));
}
catch (\Exception $e) {
  dpm($e->getTrace(), $e->getMessage());
}
