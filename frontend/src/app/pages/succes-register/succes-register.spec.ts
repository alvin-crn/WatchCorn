import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SuccesRegister } from './succes-register';

describe('SuccesRegister', () => {
  let component: SuccesRegister;
  let fixture: ComponentFixture<SuccesRegister>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SuccesRegister]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SuccesRegister);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
