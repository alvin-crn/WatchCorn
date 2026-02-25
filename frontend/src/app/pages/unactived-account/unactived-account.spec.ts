import { ComponentFixture, TestBed } from '@angular/core/testing';

import { UnactivedAccount } from './unactived-account';

describe('UnactivedAccount', () => {
  let component: UnactivedAccount;
  let fixture: ComponentFixture<UnactivedAccount>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [UnactivedAccount]
    })
    .compileComponents();

    fixture = TestBed.createComponent(UnactivedAccount);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
