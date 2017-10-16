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
          'postal code' => 'M22 7HJ',
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
  );
  $service = \Drupal::service('sowlo.candidate_importer');
  $candidate = $service->importSingle(json_decode(json_encode($data)));
}
catch (\Exception $e) {
  dpm($e->getTrace(), $e->getMessage());
}
