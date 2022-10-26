<?php

it('can generate markdown with front matter', function ($front_matter, $expected_yaml) {
    $markdown_body = "# Test";

    $creator = \AloiaCms\Writer\FrontMatterCreator::seed($front_matter, $markdown_body);

    expect($creator->create())->toBe($expected_yaml);
})->with([
    [
        ['test' => true],
        "---\ntest: true\n---\n# Test"
    ],
    [
        ['TestCases' => 'test'],
        "---\ntest_cases: test\n---\n# Test"
    ],
    [
        ['_testAttribute' => 'test'],
        "---\n_test_attribute: test\n---\n# Test"
    ]
]);
