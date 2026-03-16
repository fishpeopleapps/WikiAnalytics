CREATE TABLE monthly_namespace_breakdown (
  mnb_year INT NOT NULL,
  mnb_month INT NOT NULL,
  namespace_id INT NOT NULL,
  page_count INT NOT NULL,
  edit_count INT NOT NULL,

  PRIMARY KEY (mnb_year, mnb_month, namespace_id)
);
